<?php
/**
 * Meetup GraphQL Wrapper.
 *
 * @author Rajat Patel
 */

/**
 * Meetup GraphQL Wrapper class.
 *
 * @category   Class
 */
class WP_Event_Aggregator_Meetup_API {

    /**
     * Contain Meetup GraphQL URL
     * @access private
     */
    private $api_url = 'https://api.meetup.com/gql-ext';

    /**
     * Contain Meetup API Key
     * @access private
     */
    private $api_key;


    /**
     * Initialize Meetup GraphQL.
     *
     * @param string $access_token    Acccess Token OR key
     */
    public function __construct( $api_key = '' ){
        if ( empty( $api_key ) ) {
            $access_token   = get_option('wpea_muser_token_options', false );
            $api_key        = get_option('wpea_options', true);

            if ( ! empty( $access_token ) ) {
                $api_key = $access_token->access_token;
            } else {
                $api_key = $api_key['meetup']['meetup_api_key'];
            }
        }
        $this->api_key = $api_key;
    }

    /**
     * Get Meetup Event Query
     * @access private
     */
    private function getEventQuery(){
        // phpcs:ignore Squiz.PHP.Heredoc.NotAllowed
        return <<<'GRAPHQL'
                query ($eventId: ID!) {
                    event(id: $eventId) {
                        id
                        title
                        dateTime
                        endTime
                        description
                        eventUrl
                        status
                        venues{
                            id
                            name
                            address
                            city
                            state
                            country
                            lat
                            lon
                            postalCode
                            venueType
                        }
                        series{
                            endDate
                            description
                        }
                        featuredEventPhoto{
                            id
                            baseUrl
                            thumbUrl
                            standardUrl
                            highResUrl
                        }
                        eventType
                        eventHosts{
                            memberId
                            name
                            memberPhoto{
                                id
                                standardUrl
                                highResUrl
                            }
                        }
                        group{
                            id
                            name
                            description
                            emailAnnounceAddress
                            urlname
                            keyGroupPhoto {
                                id
                                standardUrl
                            }
                        }
                    }
                }
GRAPHQL;
    }

    /**
     * Get Meetup Group Event Query
     * @access private
     */
    private function getGroupEventsQuery(){
        // phpcs:ignore Squiz.PHP.Heredoc.NotAllowed
        return <<<'GRAPHQL'
            query ($urlname: String!, $itemsNum: Int!, $cursor: String) {
                groupByUrlname(urlname: $urlname) {
                    events(first: $itemsNum, after: $cursor ){
                        pageInfo{
                            hasNextPage
                            endCursor
                        }
                        totalCount
                        edges {
                            node {
                                id
                                title
                                dateTime
                                endTime
                                description
                                eventUrl
                                status
                                venues{
                                    id
                                    name
                                    address
                                    city
                                    state
                                    country
                                    lat
                                    lon
                                    postalCode
                                    venueType
                                }
                                series{
                                    endDate
                                    description
                                }
                                featuredEventPhoto{
                                    id
                                    baseUrl
                                    thumbUrl
                                    standardUrl
                                    highResUrl
                                }
                                eventType
                                eventHosts{
                                    memberId
                                    name
                                    memberPhoto{
                                        id
                                        standardUrl
                                        highResUrl
                                    }
                                }
                                group{
                                    id
                                    name
                                    description
                                    emailAnnounceAddress
                                    urlname
                                    keyGroupPhoto {
                                        id
                                        standardUrl
                                    }
                                }
                            }
                        }
                    }
                }
            }
GRAPHQL;
    }

    /**
     * Get Meetup Event by Id.
     *
     * @return array Meetup Event array.
     */
    public function getEvent( $event_id = 0 ){         
        $query = $this->getEventQuery();
        $variables = ['eventId' => $event_id];
        return $this->graphql_query( $this->api_url, $query, $variables );
    }

    /**
     * Get Meetup Events By Group ID With pagination
     *
     * @return array Group ID
     */
    public function getGroupEvents( $meetup_group_id = '', $itemsNum = 0, $cursor = '' ){
        $query = $this->getGroupEventsQuery();
        $variables = ['urlname' => $meetup_group_id, 'itemsNum' => $itemsNum, 'cursor'=> $cursor ];
        return $this->graphql_query( $this->api_url, $query, $variables );
    }

    /**
     * Get Meetup Authorized User Data
     * 
     * @return array User data
     */
    public function getGroupName(  $meetup_group_id = '' ){
        // phpcs:ignore Squiz.PHP.Heredoc.NotAllowed
        $query = <<<'GRAPHQL'
        query ($urlname: String!) {
            groupByUrlname(urlname: $urlname) {
                id
                name
            }
        }
GRAPHQL;
        $variables = [ 'urlname' => $meetup_group_id ];
        return $this->graphql_query( $this->api_url, $query, $variables );
    }

    /**
     * Get Meetup Authorized User Data
     * 
     * @return array User data
     */
    public function getAuthUser(){
        // phpcs:ignore Squiz.PHP.Heredoc.NotAllowed
        $query = <<<'GRAPHQL'
            query{
                self{
                    id
                    email
                    name
                }
            }
GRAPHQL;
        $variables = [];
        return $this->graphql_query( $this->api_url, $query, $variables );
    }

    /**
     * grapgql_query function.
     *
     * @access protected
     * @return cURL object
     */
    public function graphql_query( $endpoint,  $query,  $variables = [] ) {
        $headers = ['Content-Type: application/json'];
        if (null !== $this->api_key) {
            $headers[] = 'Authorization: Bearer ' . $this->api_key;
        }

        $payload = ['query' => $query];
        if (!empty($variables)) {
			$payload['variables'] = $variables;
		}

		$json_data = json_encode($payload);
		if ($json_data === false) {
			throw new Exception('JSON encode error: ' . json_last_error_msg());
		}

		$context = stream_context_create([
			'http' => [
				'method'  => 'POST',
				'header'  => implode("\r\n", $headers),
				'content' => $json_data,
			]
		]);

		$data = @file_get_contents($endpoint, false, $context);

		if (false === $data) {
			$error = error_get_last();
			throw new ErrorException('HTTP Request Failed: ' . esc_html($error['message']), intval($error['type']));
		}

		$response = json_decode($data, true);
		if (isset($response['errors'])) {
			error_log('GraphQL API returned errors: ' . print_r($response['errors'], true));
		}

		return $response;
	}
}