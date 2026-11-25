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
                    topics {
                        edges {
                            node {
                                id
                                name
                                urlkey
                            }
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
                        topicCategory {
                            id
                            name
                            urlkey
                            color
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
                                topics {
                                    edges {
                                        node {
                                            id
                                            name
                                            urlkey
                                        }
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
                                    topicCategory {
                                        id
                                        name
                                        urlkey
                                        color
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
     * Get Meetup Group Past Event Query
     * @access private
     */
    private function getGroupPastEventsQuery(){
        return <<<'GRAPHQL'
            query getPastGroupEvents($urlname: String!, $itemsNum: Int!, $cursor: String) {
                groupByUrlname(urlname: $urlname) {
                    pastEvents: events(first: $itemsNum, after: $cursor, filter: { status: PAST }, sort: DESC) {
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
                                topics {
                                    edges {
                                        node {
                                            id
                                            name
                                            urlkey
                                        }
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
                                    topicCategory {
                                        id
                                        name
                                        urlkey
                                        color
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
     * Get Meetup Past Events By Group ID With pagination
     *
     * @return array Group ID
     */
    public function getGroupPastEvents( $meetup_group_id = '', $itemsNum = 0, $cursor = '' ){
        $query   = $this->getGroupPastEventsQuery();
        $payload = [
            "operationName" => "getPastGroupEvents",
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
     * Get Meetup Group feedback (reviews)
     *
     * @param string $group_urlname Group slug.
     * @param int    $first         Items per page.
     * @param string $cursor        Pagination cursor (endCursor).
     * @return array|null
     */
    public function getGroupFeedbackInfo( $group_urlname = '', $first = 50, $cursor = null ){
        $variables = [
            "groupUrlname" => $group_urlname,
            "first"        => (int) $first,
        ];
        if ( ! empty( $cursor ) ) {
            $variables["after"] = $cursor;
        }
        $payload = [
            "operationName" => "getGroupFeedbackInfo",
            "variables"     => $variables,
            "extensions"    => [
                "persistedQuery" => [
                    "version"    => 1,
                    "sha256Hash" => "ddc5bfb38fcec6927425fdebf0b64c4be00eccf8c39a1356867048323fcd7bce",
                ],
            ],
        ];
        return $this->graphql_query( $this->api_url, $payload );
    }

    /**
     * Get Meetup Group reviews (eventReviews)
     *
     * @param string $group_urlname Group slug.
     * @param int    $first         Items per page (max 20).
     * @param string $cursor        Pagination cursor (endCursor).
     * @return array|null
     */
    public function getGroupReviews( $group_urlname = '', $first = 20, $cursor = null ){
        $query = '
        query getGroupReviews($groupUrlname: String!, $first: Int!, $after: String) {
            groupByUrlname(urlname: $groupUrlname) {
              id
              urlname
              stats {
                eventRatings {
                  average
                  totalReviews
                  totalRatings
                }
              }
              eventReviews(first: $first, after: $after) {
                totalCount
                pageInfo {
                  hasNextPage
                  endCursor
                }
                edges {
                  node {
                    responseId
                    member {
                      id
                      name
                      memberPhoto {
                        id
                        baseUrl
                        highResUrl
                        thumbUrl
                        standardUrl
                      }
                    }
                    event {
                      id
                      title
                      eventUrl
                    }
                    rating
                    createDate
                    comment
                    freeTextFeedback
                    details {
                      membersLiked {
                        topic
                      }
                    }
                  }
                }
              }
            }
        }';

        $variables = [
            "groupUrlname" => $group_urlname,
            "first"        => (int) $first,
        ];
        if ( ! empty( $cursor ) ) {
            $variables["after"] = $cursor;
        }
        $payload = [
            "operationName" => "getGroupReviews",
            "query"         => $query,
            "variables"     => $variables,
        ];
        return $this->graphql_query( $this->api_url, $payload );
    }

    /**
     * Get Meetup Authorized User Data
     * 
     * @return array User data
     */
    public function getGroupName(  $meetup_group_id = '' ){
        $payload = [
            "operationName" => "getGroupInfo",
            "variables" => [
                "urlname" => $meetup_group_id
            ],
            // phpcs:ignore PluginCheck.CodeAnalysis.Heredoc.NotAllowed
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
        $response = wp_remote_post( $endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode( $payload ),
            'timeout' => 30, // optional, adjust as needed
        ] );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        $json = json_decode( $body, true );

        return $json ?? null;
    }
}