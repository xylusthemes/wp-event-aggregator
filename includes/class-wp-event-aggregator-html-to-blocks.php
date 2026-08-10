<?php
/**
 * HTML to Gutenberg Blocks converter for WP Event Aggregator.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Event_Aggregator_Html_To_Blocks {

	/**
	 * Convert HTML string to Gutenberg block markup.
	 *
	 * @since  1.0.0
	 * @param  string $html Raw HTML from source API.
	 * @return string       Gutenberg block markup.
	 */
	public function convert( string $html ): string {
		if ( empty( trim( $html ) ) ) {
			return '';
		}

		$dom = $this->get_dom( $html );
		if ( ! $dom ) {
			return $this->freeform_block( $html );
		}

		$blocks = '';
		foreach ( $dom->childNodes as $node ) {
			$blocks .= $this->node_to_block( $node, $dom );
		}

		$blocks = trim( $blocks );

		// Fallback: if nothing converted, wrap as freeform.
		return $blocks !== '' ? $blocks : $this->freeform_block( $html );
	}

	/**
	 * Convert a post's content to Gutenberg blocks via Action Scheduler.
	 * Safely skips already-converted posts and deleted/trashed posts.
	 *
	 * Usage:
	 *   add_action( 'wpea_convert_single_event_to_blocks', [ $instance, 'convert_post' ] );
	 *
	 * @since  1.0.0
	 * @param  int $post_id WordPress post ID.
	 * @return void
	 */
	public function convert_post( int $post_id ): void {
		$post = get_post( $post_id );

		// CHECK 1: Post must exist and not be trashed.
		if ( ! $post || $post->post_status === 'trash' ) {
			return;
		}

		// CHECK 2: Skip if already converted to blocks.
		if ( false !== strpos( $post->post_content, '<!-- wp:' ) ) {
			return;
		}

		$blocks = $this->convert( $post->post_content );

		if ( empty( $blocks ) ) {
			return;
		}

		wp_update_post( [
			'ID'           => $post_id,
			'post_content' => $blocks,
		] );
	}

	// -------------------------------------------------------------------------
	// Private: DOM Helpers
	// -------------------------------------------------------------------------

	/**
	 * Load HTML into a DOMDocument safely.
	 *
	 * @since  1.0.0
	 * @param  string $html
	 * @return DOMDocument|false
	 */
	private function get_dom( string $html ) {
		$previous = libxml_use_internal_errors( true );

		$dom    = new DOMDocument( '1.0', 'UTF-8' );
		$loaded = $dom->loadHTML(
			'<?xml encoding="UTF-8">' . $html,
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);

		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		return $loaded ? $dom : false;
	}

	/**
	 * Get inner HTML of a DOMNode (preserves inline tags).
	 *
	 * @since  1.0.0
	 * @param  DOMNode     $node
	 * @param  DOMDocument $dom
	 * @return string
	 */
	private function get_inner_html( DOMNode $node, DOMDocument $dom ): string {
		$html = '';
		foreach ( $node->childNodes as $child ) {
			$html .= $dom->saveHTML( $child );
		}
		return $html;
	}

	/**
	 * Get <li> items from a list DOMNode.
	 *
	 * @since  1.0.0
	 * @param  DOMNode     $node
	 * @param  DOMDocument $dom
	 * @return string[]
	 */
	private function get_list_items( DOMNode $node, DOMDocument $dom ): array {
		$items = [];
		foreach ( $node->childNodes as $child ) {
			if (
				$child->nodeType === XML_ELEMENT_NODE &&
				strtolower( $child->nodeName ) === 'li'
			) {
				$items[] = trim( $this->get_inner_html( $child, $dom ) );
			}
		}
		return $items;
	}

	// -------------------------------------------------------------------------
	// Private: Node → Block Dispatcher
	// -------------------------------------------------------------------------

	/**
	 * Convert a single DOMNode to a Gutenberg block string.
	 *
	 * @since  1.0.0
	 * @param  DOMNode     $node
	 * @param  DOMDocument $dom
	 * @return string
	 */
	private function node_to_block( DOMNode $node, DOMDocument $dom ): string {
		// Plain text nodes.
		if ( $node->nodeType === XML_TEXT_NODE ) {
			return $this->text_node_to_block( $node );
		}

		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return '';
		}

		$tag   = strtolower( $node->nodeName );
		$inner = trim( $this->get_inner_html( $node, $dom ) );

		switch ( $tag ) {

			// ── Text blocks ──────────────────────────────────────────────────────────
			case 'p':
				return $this->paragraph_block( $inner );

			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
				return $this->heading_block( $tag, $inner );

			case 'blockquote':
				return $this->quote_block( $inner );

			case 'pre':
			case 'code':
				return $this->code_block( $node->textContent );

			// ── Lists ────────────────────────────────────────────────────────────────
			case 'ul':
				return $this->list_block( $node, $dom, false );

			case 'ol':
				return $this->list_block( $node, $dom, true );

			case 'dl': // definition list → fallback to freeform
				return $this->freeform_block( $dom->saveHTML( $node ) );

			// ── Media ────────────────────────────────────────────────────────────────
			case 'img':
				return $this->image_block( $node );

			case 'figure':
				return $this->figure_block( $node, $dom );

			case 'picture': // <picture> wraps <source> + <img>
				$img = $node->getElementsByTagName( 'img' )->item( 0 );
				return $img ? $this->image_block( $img ) : '';

			case 'video': // native <video> tag
				return $this->video_block( $node );

			case 'audio': // native <audio> tag
				return $this->audio_block( $node );

			case 'iframe':
				return $this->embed_block( $node );

			case 'embed': // old-school <embed> tag
			case 'object': // old-school <object> tag
				$src = $node->getAttribute( 'src' ) ?: $node->getAttribute( 'data' );
				if ( $src ) {
					$provider = $this->detect_embed_provider( $src );
					$watch    = $this->embed_url_to_watch_url( $src, $provider );
					return $this->embed_block_from_url( $watch, $provider );
				}
				return '';

			// ── Table ────────────────────────────────────────────────────────────────
			case 'table':
				return $this->table_block( $dom->saveHTML( $node ) );

			// ── Structural / separators ──────────────────────────────────────────────
			case 'hr':
				return $this->separator_block();

			// ── Transparent containers → recurse into children ───────────────────────
			case 'div':
			case 'section':
			case 'article':
			case 'main':
			case 'header':
			case 'footer':
			case 'aside':
			case 'nav':
			case 'form':      // forms sometimes appear in event descriptions
			case 'details':
			case 'summary':
			case 'fieldset':
			case 'label':
			case 'span':
			case 'body':
			case 'html':
				return $this->recurse_children( $node, $dom );

			// ── Inline-only tags that should never be block-level ────────────────────
			// These appear as direct children only when HTML is malformed.
			// Wrap their text content in a paragraph.
			case 'a':      // stray <a> outside <p>
			case 'strong': // stray <strong> outside <p>
			case 'b':
			case 'em':
			case 'i':
			case 'u':
			case 'small':
			case 'mark':
			case 'del':
			case 'ins':
			case 'sub':
			case 'sup':
			case 'abbr':
			case 'cite':
			case 'q': // inline quote
				return $inner !== '' ? $this->paragraph_block( $inner ) : '';

			// ── Purely ignored tags ──────────────────────────────────────────────────
			case 'br':       // handled inside get_inner_html via saveHTML
			case 'wbr':      // word-break opportunity
			case 'head':
			case 'meta':
			case 'link':
			case 'title':
			case 'script':
			case 'style':
			case 'noscript':
			case 'template':
			case 'input':    // stray form elements
			case 'button':
			case 'select':
			case 'textarea':
			case 'option':
			case 'svg':      // skip SVG blobs
			case 'canvas':
			case 'map':
			case 'area':
				return '';

			// ── Unknown / future tags ────────────────────────────────────────────────
			default:
				return $inner !== '' ? $this->paragraph_block( $inner ) : '';
		}
	}

	/**
	 * Recurse into child nodes of a container element.
	 *
	 * @since  1.0.0
	 * @param  DOMNode     $node
	 * @param  DOMDocument $dom
	 * @return string
	 */
	private function recurse_children( DOMNode $node, DOMDocument $dom ): string {
		$output = '';
		foreach ( $node->childNodes as $child ) {
			$output .= $this->node_to_block( $child, $dom );
		}
		return $output;
	}

	// -------------------------------------------------------------------------
	// Private: Block Builders
	// -------------------------------------------------------------------------

	/**
	 * Plain text node → paragraph block.
	 *
	 * @since  1.0.0
	 * @param  DOMNode $node
	 * @return string
	 */
	private function text_node_to_block( DOMNode $node ): string {
		$text = trim( $node->textContent );
		if ( $text === '' ) {
			return '';
		}
		return $this->paragraph_block( esc_html( $text ) );
	}

	/**
	 * Build a core/paragraph block.
	 *
	 * @since  1.0.0
	 * @param  string $inner Inner HTML content.
	 * @return string
	 */
	private function paragraph_block( string $inner ): string {
		if ( $inner === '' ) {
			return '';
		}
		return "<!-- wp:paragraph -->\n<p>{$inner}</p>\n<!-- /wp:paragraph -->\n\n";
	}

	/**
	 * Build a core/heading block.
	 *
	 * @since  1.0.0
	 * @param  string $tag   HTML tag, e.g. 'h2'.
	 * @param  string $inner Inner HTML content.
	 * @return string
	 */
	private function heading_block( string $tag, string $inner ): string {
		if ( $inner === '' ) {
			return '';
		}
		$level = (int) substr( $tag, 1 );
		return "<!-- wp:heading {\"level\":{$level}} -->\n<h{$level} class=\"wp-block-heading\">{$inner}</h{$level}>\n<!-- /wp:heading -->\n\n";
	}

	/**
	 * Build a core/list block (supports nested lists).
	 *
	 * @since  1.0.0
	 * @param  DOMNode     $node
	 * @param  DOMDocument $dom
	 * @param  bool        $ordered
	 * @return string
	 */
	private function list_block( DOMNode $node, DOMDocument $dom, bool $ordered ): string {
		$items = [];

		foreach ( $node->childNodes as $child ) {
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}
			if ( strtolower( $child->nodeName ) !== 'li' ) {
				continue;
			}

			$li_content = '';
			foreach ( $child->childNodes as $li_child ) {
				$li_tag = strtolower( $li_child->nodeName ?? '' );

				// Nested list — recurse.
				if ( $li_tag === 'ul' ) {
					$li_content .= $this->list_block( $li_child, $dom, false );
				} elseif ( $li_tag === 'ol' ) {
					$li_content .= $this->list_block( $li_child, $dom, true );
				} else {
					$li_content .= $dom->saveHTML( $li_child );
				}
			}
			$items[] = trim( $li_content );
		}

		if ( empty( $items ) ) {
			return '';
		}

		$li_html  = implode( "\n", array_map( fn( $i ) => "<!-- wp:list-item -->\n\t<li>{$i}</li>\n<!-- /wp:list-item -->", $items ) );
		$tag_open = $ordered ? 'ol' : 'ul';
		$tag_attr = $ordered ? ' {"ordered":true}' : '';

		return "<!-- wp:list{$tag_attr} -->\n<{$tag_open} class=\"wp-block-list\">\n{$li_html}\n</{$tag_open}>\n<!-- /wp:list -->\n\n";
	}

	/**
	 * Build a core/image block from an <img> node.
	 *
	 * @since  1.0.0
	 * @param  DOMNode $node
	 * @return string
	 */
	private function image_block( DOMNode $node ): string {
		$src = esc_url( $node->getAttribute( 'src' ) );
		if ( ! $src ) {
			return '';
		}
		$alt    = esc_attr( $node->getAttribute( 'alt' ) );
		$width  = esc_attr( $node->getAttribute( 'width' ) );
		$height = esc_attr( $node->getAttribute( 'height' ) );

		$attrs_json = '';
		if ( $width && $height ) {
			$attrs_json = ' ' . wp_json_encode( [ 'width' => (int) $width, 'height' => (int) $height ] );
		}

		return "<!-- wp:image{$attrs_json} -->\n<figure class=\"wp-block-image\"><img src=\"{$src}\" alt=\"{$alt}\"/></figure>\n<!-- /wp:image -->\n\n";
	}

	/**
	 * Handle <figure> — may contain <img> or <figcaption>.
	 *
	 * @since  1.0.0
	 * @param  DOMNode     $node
	 * @param  DOMDocument $dom
	 * @return string
	 */
	private function figure_block( DOMNode $node, DOMDocument $dom ): string {
		$src     = '';
		$alt     = '';
		$caption = '';

		foreach ( $node->childNodes as $child ) {
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}
			$child_tag = strtolower( $child->nodeName );
			if ( $child_tag === 'img' ) {
				$src = esc_url( $child->getAttribute( 'src' ) );
				$alt = esc_attr( $child->getAttribute( 'alt' ) );
			} elseif ( $child_tag === 'figcaption' ) {
				$caption = trim( $this->get_inner_html( $child, $dom ) );
			}
		}

		if ( ! $src ) {
			// No image found — recurse normally.
			return $this->recurse_children( $node, $dom );
		}

		$caption_html = $caption !== ''
			? "<figcaption class=\"wp-element-caption\">{$caption}</figcaption>"
			: '';

		return "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img src=\"{$src}\" alt=\"{$alt}\"/>{$caption_html}</figure>\n<!-- /wp:image -->\n\n";
	}

	/**
	 * Build a core/quote block.
	 *
	 * @since  1.0.0
	 * @param  string $inner Inner HTML content.
	 * @return string
	 */
	private function quote_block( string $inner ): string {
		if ( $inner === '' ) {
			return '';
		}

		// If inner HTML already contains <p> tags (e.g. source sends
		// <blockquote><p><em>"text"</em></p></blockquote>), don't double-wrap.
		if ( stripos( $inner, '<p>' ) !== false ) {
			return "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\">{$inner}</blockquote>\n<!-- /wp:quote -->\n\n";
		}

		return "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>{$inner}</p></blockquote>\n<!-- /wp:quote -->\n\n";
	}

	/**
	 * Build a core/separator block.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	private function separator_block(): string {
		return "<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->\n\n";
	}

	/**
	 * Build a core/code block.
	 *
	 * @since  1.0.0
	 * @param  string $content Raw text content.
	 * @return string
	 */
	private function code_block( string $content ): string {
		if ( trim( $content ) === '' ) {
			return '';
		}
		$escaped = esc_html( $content );
		return "<!-- wp:code -->\n<pre class=\"wp-block-code\"><code>{$escaped}</code></pre>\n<!-- /wp:code -->\n\n";
	}

	/**
	 * Build a core/table block.
	 *
	 * @since  1.0.0
	 * @param  string $raw_html Raw <table> HTML.
	 * @return string
	 */
	private function table_block( string $raw_html ): string {
		return "<!-- wp:table -->\n<figure class=\"wp-block-table\">{$raw_html}</figure>\n<!-- /wp:table -->\n\n";
	}

	/**
	 * Wrap raw HTML in a core/html (Custom HTML) freeform block.
	 * Used as a last resort fallback.
	 *
	 * @since  1.0.0
	 * @param  string $html
	 * @return string
	 */
	private function freeform_block( string $html ): string {
		return "<!-- wp:html -->\n{$html}\n<!-- /wp:html -->\n";
	}

	/**
	 * Build a core/embed block from an <iframe> node.
	 * Detects YouTube, Vimeo, etc. and uses the correct provider variant.
	 *
	 * @since  1.0.0
	 * @param  DOMNode $node
	 * @return string
	 */
	private function embed_block( DOMNode $node ): string {
		$src = trim( $node->getAttribute( 'src' ) );

		if ( empty( $src ) ) {
			return '';
		}

		// Normalize: strip trailing slash from source URLs.
		$src = rtrim( $src, '/' );

		// Detect provider and build correct block attributes.
		$provider_slug = $this->detect_embed_provider( $src );

		// core/embed expects the WATCH url, not the embed url.
		$watch_url = $this->embed_url_to_watch_url( $src, $provider_slug );

		$attrs = wp_json_encode( [
			'url'              => $watch_url,
			'type'             => 'video',
			'providerNameSlug' => $provider_slug,
			'responsive'       => true,
		] );

		return "<!-- wp:embed {$attrs} -->\n"
			. "<figure class=\"wp-block-embed is-type-video is-provider-{$provider_slug} wp-block-embed-{$provider_slug}\">"
			. "<div class=\"wp-block-embed__wrapper\">\n{$watch_url}\n</div>"
			. "</figure>\n"
			. "<!-- /wp:embed -->\n\n";
	}

	/**
	 * Detect the embed provider slug from a URL.
	 *
	 * @since  1.0.0
	 * @param  string $url
	 * @return string Provider slug, e.g. 'youtube', 'vimeo', 'generic'.
	 */
	private function detect_embed_provider( string $url ): string {
		if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {
			return 'youtube';
		}
		if ( strpos( $url, 'vimeo.com' ) !== false ) {
			return 'vimeo';
		}
		if ( strpos( $url, 'dailymotion.com' ) !== false ) {
			return 'dailymotion';
		}
		if ( strpos( $url, 'twitter.com' ) !== false || strpos( $url, 'x.com' ) !== false ) {
			return 'twitter';
		}
		return 'generic';
	}

	/**
	 * Convert an embed/iframe URL to a canonical watch URL.
	 * e.g. https://www.youtube.com/embed/ABC123 → https://www.youtube.com/watch?v=ABC123
	 *
	 * @since  1.0.0
	 * @param  string $embed_url
	 * @param  string $provider
	 * @return string
	 */
	private function embed_url_to_watch_url( string $embed_url, string $provider ): string {
		switch ( $provider ) {

			case 'youtube':
				// https://www.youtube.com/embed/VIDEO_ID → watch?v=VIDEO_ID
				if ( preg_match( '#youtube\.com/embed/([a-zA-Z0-9_-]+)#', $embed_url, $m ) ) {
					return 'https://www.youtube.com/watch?v=' . $m[1];
				}
				return $embed_url;

			case 'vimeo':
				// https://player.vimeo.com/video/VIDEO_ID → https://vimeo.com/VIDEO_ID
				if ( preg_match( '#vimeo\.com/video/(\d+)#', $embed_url, $m ) ) {
					return 'https://vimeo.com/' . $m[1];
				}
				return $embed_url;

			default:
				return $embed_url;
		}
	}

	/**
	 * Build a core/video block from a <video> tag.
	 *
	 * @since  1.0.0
	 * @param  DOMNode $node
	 * @return string
	 */
	private function video_block( DOMNode $node ): string {
		// Try src attribute first, then first <source> child.
		$src = $node->getAttribute( 'src' );
		if ( ! $src ) {
			$sources = $node->getElementsByTagName( 'source' );
			if ( $sources->length > 0 ) {
				$src = $sources->item( 0 )->getAttribute( 'src' );
			}
		}
		if ( ! $src ) {
			return '';
		}
		$src = esc_url( $src );
		return "<!-- wp:video -->\n<figure class=\"wp-block-video\"><video controls src=\"{$src}\"></video></figure>\n<!-- /wp:video -->\n\n";
	}

	/**
	 * Build a core/audio block from an <audio> tag.
	 *
	 * @since  1.0.0
	 * @param  DOMNode $node
	 * @return string
	 */
	private function audio_block( DOMNode $node ): string {
		$src = $node->getAttribute( 'src' );
		if ( ! $src ) {
			$sources = $node->getElementsByTagName( 'source' );
			if ( $sources->length > 0 ) {
				$src = $sources->item( 0 )->getAttribute( 'src' );
			}
		}
		if ( ! $src ) {
			return '';
		}
		$src = esc_url( $src );
		return "<!-- wp:audio -->\n<figure class=\"wp-block-audio\"><audio controls src=\"{$src}\"></audio></figure>\n<!-- /wp:audio -->\n\n";
	}

	/**
	 * Build a core/embed block directly from a resolved URL + provider.
	 * Used by <embed> and <object> tags.
	 *
	 * @since  1.0.0
	 * @param  string $url
	 * @param  string $provider_slug
	 * @return string
	 */
	private function embed_block_from_url( string $url, string $provider_slug ): string {
		$attrs = wp_json_encode( [
			'url'              => $url,
			'type'             => 'video',
			'providerNameSlug' => $provider_slug,
			'responsive'       => true,
		] );
		return "<!-- wp:embed {$attrs} -->\n"
			. "<figure class=\"wp-block-embed is-type-video is-provider-{$provider_slug} wp-block-embed-{$provider_slug}\">"
			. "<div class=\"wp-block-embed__wrapper\">\n{$url}\n</div>"
			. "</figure>\n"
			. "<!-- /wp:embed -->\n\n";
	}
}