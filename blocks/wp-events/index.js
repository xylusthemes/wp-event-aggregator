const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const {
	PanelBody,
	PanelRow,
	Button,
	Dropdown,
	RangeControl,
	SelectControl,
	ToggleControl,
	RadioControl,
	DateTimePicker,
	ServerSideRender,
} = wp.components;
const { InspectorControls } = wp.editor;
const { dateI18n, __experimentalGetSettings } = wp.date;
const { createElement } = wp.element;

/**
 * Register: Facebook Events Gutenberg Block.
 */
registerBlockType( 'wpea-block/wp-events', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'WP Events' ),
	description: __( 'Block for Display WP Events' ),
	// icon: 'wordpress',
	icon: {
		foreground: '#0073AA',
		src: <svg viewBox="0 0 24 24"><g><path d="M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.05-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.762-7.57zM3.008 12c0 3.56 2.07 6.634 5.068 8.092L3.788 8.342c-.5 1.117-.78 2.354-.78 3.658zm15.06-.454c0-1.112-.398-1.88-.74-2.48-.456-.74-.883-1.368-.883-2.11 0-.825.627-1.595 1.51-1.595.04 0 .078.006.116.008-1.598-1.464-3.73-2.36-6.07-2.36-3.14 0-5.904 1.613-7.512 4.053.21.008.41.012.58.012.94 0 2.395-.114 2.395-.114.484-.028.54.684.057.74 0 0-.487.058-1.03.086l3.275 9.74 1.968-5.902-1.4-3.838c-.485-.028-.944-.085-.944-.085-.486-.03-.43-.77.056-.742 0 0 1.484.114 2.368.114.94 0 2.397-.114 2.397-.114.486-.028.543.684.058.74 0 0-.488.058-1.03.086l3.25 9.665.897-2.997c.456-1.17.684-2.137.684-2.907zm1.82-3.86c.04.286.06.593.06.924 0 .912-.17 1.938-.683 3.22l-2.746 7.94c2.672-1.558 4.47-4.454 4.47-7.77 0-1.564-.4-3.033-1.1-4.314zM12 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"></path></g></svg>,
	},
	category: 'widgets',
	keywords: [
		__( 'Events' ),
		__( 'WP' ),
		__( 'wp events' ),
	],

	// Enable or disable support for features
	supports: {
		html: false,
	},

	// Set for each piece of dynamic data used in your block
	attributes: {
		col: {
			type: 'number',
			default: 3,
		},
		posts_per_page: {
			type: 'number',
			default: 12,
		},
		past_events: {
			type: 'string',
		},
		start_date: {
			type: 'string',
			default: '',
		},
		end_date: {
			type: 'string',
			default: '',
		},
		order: {
			type: 'string',
			default: 'ASC',
		},
		orderby: {
			type: 'string',
			default: 'event_start_date',
		},
	},

	// Determines what is displayed in the editor
	edit: function( props ) {
		const { attributes, isSelected, setAttributes } = props;
		const settings = __experimentalGetSettings();
		const dateClassName = attributes.past_events === 'yes' ? 'wpea_hidden' : '';

		// To know if the current timezone is a 12 hour time with look for "a" in the time format
		// We also make sure this a is not escaped by a "/"
		const is12HourTime = /a(?!\\)/i.test(
			settings.formats.time
				.toLowerCase() // Test only the lower case a
				.replace( /\\\\/g, '' ) // Replace "//" with empty strings
				.split( '' ).reverse().join( '' ) // Reverse the string and test for "a" not followed by a slash
		);

		return [
			isSelected && (
				<InspectorControls key="inspector">
					<PanelBody title={ __( 'WP Events Setting' ) }>
						<RangeControl
							label={ __( 'Columns' ) }
							value={ attributes.col || 3 }
							onChange={ ( value ) => setAttributes( { col: value } ) }
							min={ 1 }
							max={ 4 }
						/>
						<RangeControl
							label={ __( 'Events per page' ) }
							value={ attributes.posts_per_page || 12 }
							onChange={ ( value ) => setAttributes( { posts_per_page: value } ) }
							min={ 1 }
							max={ 100 }
						/>
						<SelectControl
							label="Order By"
							value={ attributes.orderby }
							options={ [
								{ label: 'Event Start Date', value: 'event_start_date' },
								{ label: 'Event End Date', value: 'event_end_date' },
								{ label: 'Event Title', value: 'title' },
							] }
							onChange={ ( value ) => setAttributes( { orderby: value } ) }
						/>
						<RadioControl
							label={ __( 'Order' ) }
							selected={ attributes.order }
							options={ [
								{ label: __( 'Ascending' ), value: 'ASC' },
								{ label: __( 'Descending' ), value: 'DESC' },
							] }
							onChange={ value => setAttributes( { order: value } ) }
						/>
						<ToggleControl
							label={ __( 'Display past events' ) }
							checked={ attributes.past_events }
							onChange={ value => {
								attributes.start_date = '';
								attributes.end_date = '';
								return setAttributes( { past_events: value ? 'yes' : false } );
							}
							}
						/>
						<PanelRow className={ `wpea-start-date ${ dateClassName }` }>
							<span>{ __( 'Start Date' ) }</span>
							<Dropdown
								position="bottom left"
								contentClassName="wpea-start-date__dialog"
								renderToggle={ ( { onToggle, isOpen } ) => (
									<Button
										type="button"
										className="wpea-start-date__toggle"
										onClick={ onToggle }
										aria-expanded={ isOpen }
										isLink
									>
										{ eventDateLabel( attributes.start_date, true ) }
									</Button>
								) }
								renderContent={ () =>
									<DateTimePicker
										currentDate={ attributes.start_date !== '' ? attributes.start_date : new Date() }
										onChange={ ( value ) => setAttributes( { start_date: value } ) }
										locale={ settings.l10n.locale }
										is12Hour={ is12HourTime }
									/>
								}
							/>
						</PanelRow>
						<PanelRow className={ `wpea-end-date ${ dateClassName }` }>
							<span>{ __( 'End Date' ) }</span>
							<Dropdown
								position="bottom left"
								contentClassName="wpea-end-date__dialog"
								renderToggle={ ( { onToggle, isOpen } ) => (
									<Button
										type="button"
										className="wpea-end-date__toggle"
										onClick={ onToggle }
										aria-expanded={ isOpen }
										isLink
									>
										{ eventDateLabel( attributes.end_date ) }
									</Button>
								) }
								renderContent={ () =>
									<DateTimePicker
										currentDate={ attributes.end_date !== '' ? attributes.end_date : new Date() }
										onChange={ ( value ) => setAttributes( { end_date: value } ) }
										locale={ settings.l10n.locale }
										is12Hour={ is12HourTime }
									/>
								}
							/>
						</PanelRow>
					</PanelBody>
				</InspectorControls>
			),

			createElement( ServerSideRender, {
				block: 'wpea-block/wp-events',
				attributes: attributes,
			} ),
		];
	},

	save: function() {
		// Rendering in PHP.
		return null;
	},
} );

function eventDateLabel( date, start ) {
	const settings = __experimentalGetSettings();
	const defaultLabel = start ? __( 'Select Start Date' ) : __( 'Select End Date' );
	return date ?
		dateI18n( settings.formats.datetime, date ) :
		defaultLabel;
}
