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
class WP_Event_Aggregator_Meetup_Public_API {

/**
     * Contain Meetup GraphQL URL
     * @access private
     */
    private $api_url = 'https://www.meetup.com/gql2';

    /**
     * Initialize Meetup GraphQL.
     *
     * @param string
     */
    public function __construct(){}

    /**
     * Get Meetup Event Query
     * @access private
     */
    private function getEventQuery(){
        return <<<'GRAPHQL'
            query event($eventId: ID!) {
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
        return <<<'GRAPHQL'
            query getUpcomingGroupEvents($urlname: String!, $itemsNum: Int!, $cursor: String) {
                groupByUrlname(urlname: $urlname) {
                    events(first: $itemsNum, after: $cursor){
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
        $query   = $this->getEventQuery();
        $payload = [
            "operationName" => "event",
            "variables"     => ["eventId" => $event_id],
            "query"         => $query,
        ];

        return $this->graphql_query( $this->api_url, $payload );
    }
    
    /**
     * Get Meetup Events By Group ID With pagination
     *
     * @return array Group ID
     */
    public function getGroupEvents( $meetup_group_id = '', $itemsNum = 0, $cursor = '' ){
        $query   = $this->getGroupEventsQuery();
        $payload = [
            "operationName" => "getUpcomingGroupEvents",
            "variables" => [
                "urlname"  => $meetup_group_id,
                "itemsNum" => $itemsNum,
                "cursor"   => $cursor
            ],
            "query" => $query,
        ];

        return $this->graphql_query( $this->api_url, $payload );
    }

    /**
     * Get Meetup Authorized User Data
     * 
     * @return array User data
     */
    public function getGroupName(  $meetup_group_id = '' ){
        // phpcs:ignore Squiz.PHP.Heredoc.NotAllowed
        $payload = [
            "operationName" => "getGroupInfo",
            "variables" => [
                "urlname" => $meetup_group_id
            ],
            "query" => <<<GRAPHQL
                query getGroupInfo(\$urlname: String!) {
                    groupByUrlname(urlname: \$urlname) {
                        id
                        name
                    }
                }
            GRAPHQL
        ];
        return $this->graphql_query( $this->api_url, $payload );
    }

    /**
     * grapgql_query function.
     *
     * @access protected
     * @return cURL object
     */
    public function graphql_query( $endpoint, $payload ) {

        $ch = curl_init( $endpoint );

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"] );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );

        $response = curl_exec( $ch );
        curl_close( $ch );

        $json = json_decode( $response, true );

        return $json ?? null;
    }
}