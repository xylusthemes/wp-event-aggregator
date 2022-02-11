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
    private $api_url = 'https://api.meetup.com/gql';

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
            $access_token   = get_option('wpea_muser_token_options', true);
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
        return <<<'GRAPHQL'
                query ($eventId: ID!) {
                    event(id: $eventId) {
                        id
                        title
                        dateTime
                        endTime
                        description
                        shortDescription
                        recurrenceDescription
                        duration
                        timezone
                        eventUrl
                        status
                        venue{
                            id
                            name
                            address
                            city
                            state
                            country
                            lat
                            lng
                            postalCode
                            zoom
                        }
                        onlineVenue{
                            type
                            url
                        }
                        isOnline
                        imageUrl
                        hosts{
                            id
                            name
                            email
                            lat
                            lon
                            city
                            state
                            country
                        }
                        group{
                            id
                            name
                            description
                            emailListAddress
                            urlname
                            logo{
                                baseUrl
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
        return <<<'GRAPHQL'
            query ($urlname: String!, $itemsNum: Int!, $cursor: String) {
                groupByUrlname(urlname: $urlname) {
                    upcomingEvents(input: {first: $itemsNum, after: $cursor}){
                        pageInfo{
                            hasNextPage
                            endCursor
                        }
                        count
                        edges {
                            node {
                                id
                                title
                                dateTime
                                endTime
                                description
                                shortDescription
                                recurrenceDescription
                                duration
                                timezone
                                eventUrl
                                status
                                venue{
                                    id
                                    name
                                    address
                                    city
                                    state
                                    country
                                    lat
                                    lng
                                    postalCode
                                    zoom
                                }
                                onlineVenue{
                                    type
                                    url
                                }
                                isOnline
                                imageUrl
                                hosts{
                                    id
                                    name
                                    email
                                    lat
                                    lon
                                    city
                                    state
                                    country
                                }
                                group{
                                    id
                                    name
                                    description
                                    emailListAddress
                                    urlname
                                    logo{
                                        baseUrl
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

        if (false === $data = @file_get_contents($endpoint, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $headers,
                'content' => json_encode(['query' => $query, 'variables' => $variables]),
            ]
        ]))) {
            $error = error_get_last();
            throw new ErrorException( $error['message'], $error['type']);
        }

        return json_decode($data, true);
    }
}