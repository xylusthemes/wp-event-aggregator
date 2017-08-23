<?php
/**
 * Class for iCal Parser.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Ical_Parser {

	/**
	 * Calendar Timezone get from "X-WR-TIMEZONE"
	 *
	 * @since    1.0.0
	 */
	protected $timezone;

	/**
	 * Calendar Timezone get from "X-WR-TIMEZONE"
	 *
	 * @since    1.0.0
	 */
	protected $is_aioec_active = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		// init operations for iCal
	}

	/**
	 * parse events from ical content as per selected criteria
	 *
	 * @since  1.1.0
	 * @param  array $eventdata  import event data.
	 * @param  strine $ics_content  ics file content.
	 * @return array/boolean
	 */
	public function parse_import_events( $event_data = array(), $ics_content = '' ){

		global $wpea_errors, $importevents;
		if( empty( $ics_content ) ){
			return false;
		}
		$activate_plugins = $importevents->common->get_active_supported_event_plugins();
		if( !empty( $activate_plugins ) ){
			foreach ($activate_plugins as $key => $value) {
				if( $key == 'aioec' ){
					$this->is_aioec_active = true;			
				}
			}
		}
		$imported_events = array();

		$start_date = date('Y-m-d' );
		$end_date = date('Y-m-d', strtotime('+2 years') );
		
		if( isset( $event_data['start_date'] ) && $event_data['start_date'] != '' ){
			$start_date = $event_data['start_date'];
		}
		if( isset( $event_data['end_date'] ) && $event_data['end_date'] != '' ){
 			$end_date = $event_data['end_date'];
		}

		$start_date  = strtotime( $start_date );
		$end_date  = strtotime( $end_date );
		if( ( $end_date - $start_date ) < 0 ){
			$wpea_errors[] = esc_html__( 'Please select end
				date bigger than start date.', 'wp-event-aggregator');
			return false;
		}
		// Get Start and End date  day,month,year
		$start_month = date( 'm', $start_date );
		$start_year  = date( 'Y', $start_date );
		$start_day   = date( 'd', $start_date );
		$end_month = date( 'm', $end_date );
		$end_year  = date( 'Y', $end_date );
		$end_day   = date( 'd', $end_date );

		// initiate vcalendar
		//$config = array( 'unique_id' => 'WP_Event_Aggregator_Ical_Parser' . microtime( true ) ); 
		//$calendar = new vcalendar( $config );
		$calendar = new vcalendar();
		if ( ! $calendar ) {
			return false;
		}

		$calendar->parse( $ics_content );

		$calendar_name = $calendar->getProperty( 'X-WR-CALNAME' );		
		$timezone = $calendar->getProperty( 'X-WR-TIMEZONE' );
		if ( ! empty( $timezone[1] ) ) {
			$this->timezone = $timezone[1];
		}

		$all_events = $calendar->selectComponents( $start_year, $start_month, $start_day, $end_year, $end_month, $end_day, 'vevent' );
		$centralize_events = array();
		/*
		iCalCreator Example for parse

		$events_arr = $vcalendar->selectComponents( 2007, 11, 1, 2007, 11, 30, "vevent" ); 
		foreach( $events_arr as $year => $year_arr ) { 
			foreach( $year_arr as $month => $month_arr ) { 
				foreach( $month_arr as $day => $day_arr ) { 
					foreach( $day_arr as $event ) { 
						$currddate = $event->getProperty( "x-current-dtstart" ); 
						// if member of a recurrence set, returns
       					//array(" x-current-dtstart",
       					// <(string) date("Y-m-d [H:i:s][timezone/UTC offset]")>) 
       					$startDate = $event->getProperty( "dtstart" ); 
       					$summary = $event->getProperty( "summary" );
       					$description = $event->getProperty( "description" );
       				}
       			}
       		}
       	}
       	*/

       	// Events per Year
		foreach ( $all_events as $year => $year_arr ) {
			if ( strtotime( "$year-12-31" ) < $start_date ) {
				continue;
			}

			if ( strtotime( "$year-01-01" ) > $end_date ) {
				continue;
			}

			// Events per Month
			foreach ( $year_arr as $month => $month_arr ) {
				$mm = str_pad( $month, 2, '0', STR_PAD_LEFT );
				$d = new DateTime( "{$year}-{$mm}-01" );
				$d->setDate( $year, $mm, $d->format( 't' ) );
				// skip is event date is less then start date
				if ( $d->format( 'U' ) < $start_date ) {
					continue;
				}
				// skip is event date is greater then end date
				if ( strtotime( "{$year}-{$mm}-01" ) > $end_date ) {
					continue;
				}

				// Events per Day
				foreach ( $month_arr as $day => $day_arr ) {
					$dd = str_pad( $day, 2, '0', STR_PAD_LEFT );

					if ( strtotime( "{$year}-{$mm}-{$dd} 23:59:59" ) < $start_date ) {
						continue;
					}

					if ( strtotime( "{$year}-{$mm}-{$dd}" ) > $end_date ) {
						continue;
					}

					// Fitered Events
					foreach ( $day_arr as $event ) {
						
						$centralize_event = $this->generate_centralize_event_array( $event );
						if( !empty( $centralize_event ) ){
							$centralize_events[$centralize_event['ID']] = $centralize_event;
						}
						/*echo "<pre>";
						print_r( $event );
						print_r( $centralize_event );*/
					}
				} //end day foreach
			} //end month foreach
		} //end year foreach
		//exit();

		$imported_events = array();
		if( !empty( $centralize_events ) ){
			foreach ($centralize_events as $central_event ) {
				$imported_events[] = $importevents->common->import_events_into( $central_event, $event_data );
			}
		}
		
		return $imported_events;
	}

	/**
	 * Format events arguments as per centralize event format
	 *
	 * @since    1.0.0
	 * @param 	 array $event iCal vevent Object.
	 * @return 	 array
	 */
	public function generate_centralize_event_array( $event ) {
		if( empty( $event ) ){
			return false;
		}
		global $importevents;
		
		$is_recurrence_event = $event->getProperty( 'X-RECURRENCE' );
		$post_title = str_replace('\n', ' ', $event->getProperty( 'SUMMARY' ) );
		$post_description = str_replace('\n', '<br/>', $event->getProperty( 'DESCRIPTION' ) );
		$uid = $this->generate_uid_for_ical_event( $event );
		$uid_old = $this->generate_uid_for_ical_event_old_support( $event );
		$is_all_day = false;
		
		$system_timezone = date_default_timezone_get();
		$wordpress_timezone = $this->wordpress_timezone();
		$calendar_timezone = $this->timezone;

		$start = $event->getProperty( 'dtstart', 1, true );
		$end   = $event->getProperty( 'dtend',   1, true );
				
		if ( empty( $end ) )  {
			$end = $start;
		}

		if( ! isset( $start['value']['hour'] ) ){
			$is_all_day = true;
		}
		// Also check the proprietary MS all-day field.
		$ms_allday = $event->getProperty( 'X-MICROSOFT-CDO-ALLDAYEVENT' );
		if ( ! empty( $ms_allday ) && $ms_allday[1] == 'TRUE' ) {
			$is_all_day = true;
		}

		// Setup timezone for event.
		$timezone = null;
        $force_timezone = false;
		if ( isset( $start['value']['tz'] ) && 'Z' == $start['value']['tz'] ) {
			$timezone = 'UTC';
            if( !$is_all_day && $this->timezone != '' ){
                $force_timezone = $this->timezone;
                $timezone = $this->timezone;
            }
		} elseif ( isset( $start['params']['TZID'] ) ) {
			// if there's a TZID set
			$timezone    = $start['params']['TZID'];
			$tzid_values = explode( ':', $timezone );
			if ( 2 === count( $tzid_values ) &&	15 === strlen ( $tzid_values[1] ) ) {
				$timezone    = $tzid_values[0];
			}

		} elseif ( ! empty( $this->timezone ) ) {
			// if there is a global timezone in the iCal file, use that
			$timezone = $this->timezone;
            if( !$is_all_day ){
                $force_timezone = $this->timezone;
            }
		}

		if( $is_all_day || empty( $timezone ) ){
			$timezone = $system_timezone;
		}
		/*if( empty( $timezone ) ){
			$timezone = $system_timezone;
		}*/

		$start = $start['value'];
		$end = $end['value'];
		
		if ( empty( $start['hour'] ) ) {
			$start['hour'] = '00';
		}

		if ( empty( $start['min'] ) ) {
			$start['min'] = '00';
		}

		if ( empty( $start['sec'] ) ) {
			$start['sec'] = '00';
		}

		if ( empty( $end['hour'] ) ) {
			$end['hour'] = '00';
		}

		if ( empty( $end['min'] ) ) {
			$end['min'] = '00';
		}

		if ( empty( $end['sec'] ) ) {
			$end['sec'] = '00';
		}

		if( $is_all_day == true ){
			$start['hour'] = 00;
			$start['min']  = 00;
			$start['sec']  = 00;
			$end['hour']   = 23;
			$end['min']    = 59;
			$end['sec']    = 59;
			$end['day']  = $end['day'] - 1;
		}

		$start = sprintf( "%'.04d%'.02d%'.02dT%'.02d%'.02d%'.02d", $start['year'], $start['month'], $start['day'],$start['hour'],$start['min'],$start['sec'] );
		$end = sprintf( "%'.04d%'.02d%'.02dT%'.02d%'.02d%'.02d", $end['year'], $end['month'], $end['day'],$end['hour'],$end['min'],$end['sec'] );

		$start_time = strtotime( $this->convert_datetime_to_timezone_wise_datetime( $start, $force_timezone ) );
		$end_time = strtotime( $this->convert_datetime_to_timezone_wise_datetime( $end, $force_timezone ) );
		/*$start_time = strtotime( $start ); 
		$end_time = strtotime( $end );*/
		
		$x_start_str  = $event->getProperty( 'X-CURRENT-DTSTART' );
		$x_start_time = strtotime( $this->convert_datetime_to_timezone_wise_datetime( end( $x_start_str ), $force_timezone ) );
		//$x_start_time = strtotime( end( $x_start_str ) );
		//$x_start_str  = $this->convert_date_to_according_timezone( end( $x_start_str ), $system_timezone, $timezone );
		$x_end_str  = $event->getProperty( 'X-CURRENT-DTEND' );
		$x_end_str = end( $x_end_str );
		if( $x_end_str == '' ){
			$x_end_str = end( $x_start_str );
		}
		$x_end_time = strtotime( $this->convert_datetime_to_timezone_wise_datetime( $x_end_str ,$force_timezone ) );
		//$x_end_time = strtotime( $x_end_str );
		//$x_end_str  = $this->convert_date_to_according_timezone( end( $x_end_str ), $system_timezone, $timezone );
		
		//check event has an X-CURRENT-DTSTART the correct date will be X-CURRENT-DTSTART
		if ( ! empty( $x_start_time ) && ! empty( $x_end_time ) ) {
			$x_time_diff = ( $x_end_time - $x_start_time );
			$time_diff = ( $end_time - $start_time );
			if( $is_all_day ){
				if( $this->is_aioec_active ){
					$time_diff += 1;
				}else{
					$time_diff -= 86399;
				}				
			}
			if( $x_time_diff == $time_diff ){
				$start_time = $x_start_time;
				$end_time = $x_end_time;
                if ( $is_all_day ) {
                    if( !$this->is_aioec_active ){
                        // if all day events add 86399 secs (1 day - 1 sec) to end date.
                        $end_time += 86399;
                    }else{
                        // if all day events deduct 1 sec from end date.
                        $end_time -= 1;
                    }
                }
			}		
		}

		$xt_event = array(
			'origin'          => 'ical',
			'ID'              => $uid,
			'ID_ical_old'     => $uid_old,
			'name'            => $post_title,
			'description'     => $post_description,
			'starttime_local' => $start_time,
			'endtime_local'   => $end_time,
			'starttime'       => date('Ymd\THis', $start_time),
			'endtime'         => date('Ymd\THis', $end_time),
			'startime_utc'    => '',
			'endtime_utc'     => '',
			'timezone'        => $timezone,
			'utc_offset'      => '',
			'event_duration'  => '',
			'is_all_day'      => $is_all_day,
			'url'             => $event->getProperty( 'URL' ),
			'image_url'       => '',
		);

		$oraganizer_data = null;
		$event_location = null;

		$organizer = $event->getProperty( 'ORGANIZER' );
		if ( !empty( $organizer ) ) {
			$params = wp_parse_args( str_replace( ';', '&', $organizer ) );
			foreach ( $params as $k => $param ) {
				if ( $k == 'CN' ) {
					$oraganizer = explode( ':MAILTO:', $param);
					$oraganizer_data['ID'] = strtolower( str_replace( ' ','_', trim( preg_replace( '/^"(.*)"$/', '\1', $oraganizer[0] ) ) ) );
					$oraganizer_data['name'] = preg_replace( '/^"(.*)"$/', '\1', $oraganizer[0] );
					$oraganizer_data['email'] = preg_replace( '/^"(.*)"$/', '\1', trim( $oraganizer[1]) );
				} else {
					if ( ! empty( $param ) ) {
						$oraganizer_data[ $k ] = $param;
					}
				}
			}
		}		

		$location = str_replace('\n', ' ', $event->getProperty( 'LOCATION' ) );
		if ( !empty( $location ) ) {
			$event_location = array(
				'ID'           => strtolower( trim( stripslashes( $location ) ) ),
				'name'         => isset( $location ) ? stripslashes( $location ) : '',
				'description'  => '',
				'address_1'    => isset( $location ) ? stripslashes( $location ) : '',
				'address_2'    => '',
				'city'         => '',
				'state'        => '',
				'country'      => '',
				'zip'	       => '',
				'lat'     	   => '',
				'long'		   => '',
				'full_address' => isset( $location ) ? stripslashes( $location ) : '',
				'url'          => '',
				'image_url'    => ''
			);
		}
		
		$xt_event['organizer'] = $oraganizer_data;
		$xt_event['location'] = $event_location;
		
		return $xt_event;
	}

	/**
	 * Get location args for event
	 *
	 * @since    1.0.0
	 * @param array $event iCal vevent.
	 * @return array
	 */
	public function get_location( $event ) {

		$location = str_replace('\n', ' ', $event->getProperty( 'LOCATION' ) );
		if ( empty( $location ) ) {
			return null;
		}

		$event_location = array(
			'ID'           => strtolower( trim( stripslashes( $location ) ) ),
			'name'         => isset( $location ) ? stripslashes( $location ) : '',
			'description'  => '',
			'address_1'    => isset( $location ) ? stripslashes( $location ) : '',
			'address_2'    => '',
			'city'         => '',
			'state'        => '',
			'country'      => '',
			'zip'	       => '',
			'lat'     	   => '',
			'long'		   => '',
			'full_address' => isset( $location ) ? stripslashes( $location ) : '',
			'url'          => '',
			'image_url'    => ''
		);
		return $event_location;
	}

	/**
	 * Generate UID for ical event.
	 *
	 * @since    1.0.0
	 * @param 	 array $ical_event iCal vevent Object.
	 * @return 	 array
	 */
	public function generate_uid_for_ical_event( $ical_event ) {

		$recurrence_id = $ical_event->getProperty( 'RECURRENCE-ID' );
		if ( false === $recurrence_id && false !== $ical_event->getProperty( 'X-RECURRENCE' ) ) {
			$current_dt_start = $ical_event->getProperty( 'X-CURRENT-DTSTART' );
			$recurrence_id    = isset( $current_dt_start[1] ) ? $current_dt_start[1] : false;
		}
		return $ical_event->getProperty( 'UID' ) . $recurrence_id;

	}

	/**
	 * Generate UID for ical event old support.
	 *
	 * @since    1.0.0
	 * @param 	 array $ical_event iCal vevent Object.
	 * @return 	 array
	 */
	public function generate_uid_for_ical_event_old_support( $ical_event ) {

		$recurrence_id = $ical_event->getProperty( 'RECURRENCE-ID' );
		if ( false === $recurrence_id && false !== $ical_event->getProperty( 'X-RECURRENCE' ) ) {
			$current_dt_start = $ical_event->getProperty( 'X-CURRENT-DTSTART' );
			$recurrence_id    = isset( $current_dt_start[1] ) ? $current_dt_start[1] : false;
		}
		return $ical_event->getProperty( 'UID' ) . $recurrence_id . $ical_event->getProperty( 'SEQUENCE' );
	}

	/**
	 * Wordpress TimeZone
	 *
	 * @since    1.0.0
	 * @return 	 string.
	 */
	public function wordpress_timezone() {
		$utc_offset = get_option( 'gmt_offset' );
		$timezone   = get_option( 'timezone_string' );

		if ( ! empty( $timezone ) ) {
			return $timezone;
		}

		if ( 0 == $utc_offset ) {
			return 'UTC+0';
		} elseif ( $utc_offset < 0 ) {
			return 'UTC' . $utc_offset;
		}
		return 'UTC+' . $utc_offset;
	}

	/**
	 * Convert a system timezone datetime string to the desired event datetime string
	 *
	 * A conversion only occurs if the system timezone is NOT UTC
	 *
	 * @param string $date_string     Date string to possibly convert
	 * @param string $system_timezone System timezone
	 * @param string $event_timezoen  Timezone of event in ical feed
	 *
	 * @return string
	 */
	public function convert_date_to_according_timezone( $date_string, $system_timezone, $event_timezone ) {
		if ( 'UTC' === $system_timezone ) {
			return $date_string;
		}

		$given_string = new DateTime( $date_string );
		$given_string->setTimezone(new DateTimeZone( $event_timezone ) );
		$date_string = $given_string->format('Ymd\THis');
		return $date_string;
	}

    /**
     * Convert datetime to desired timezone.
     *
     * @param string $date_string     Date string to possibly convert
     * @param string $force_timezone timezone to be conterted
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function convert_datetime_to_timezone_wise_datetime( $datetime, $force_timezone = false ) {
        try {
            $datetime = new DateTime( $datetime );
            if( $force_timezone && $force_timezone !='' ){
                try{
                    $datetime->setTimezone(new DateTimeZone( $force_timezone ) );
                }catch ( Exception $ee ){ }
            }
            return $datetime->format( 'Y-m-d H:i:s' );
        }
        catch ( Exception $e ) {
            return $datetime;
        }
    }
}
