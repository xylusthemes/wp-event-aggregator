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

use Kigkonsult\Icalcreator\Vcalendar;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Ical_Parser_AIOEC {

	/**
	 * Calendar Timezone get from "X-WR-TIMEZONE"
	 *
	 * @since    1.0.0
	 */
	protected $timezone;

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

		$imported_events = array();

		$imported_events = array();

		if( $event_data['ical_import_by_date'] == 'sixmonths' ){
			$start_date = date('Y-m-d');
			$end_date   = date('Y-m-d', strtotime('+6 months') );
		}elseif( $event_data['ical_import_by_date'] == 'oneyear' ){
			$start_date = date('Y-m-d');
			$end_date   = date('Y-m-d', strtotime('+1 years') );
		}elseif( $event_data['ical_import_by_date'] == 'twoyears' ){
			$start_date = date('Y-m-d');
			$end_date   = date('Y-m-d', strtotime('+2 years') );
		}elseif( $event_data['ical_import_by_date'] == 'custom_date_range' ){
			if( !empty( $event_data['start_date'] ) && !empty( $event_data['end_date'] ) ){
				$start_date = $event_data['start_date'];
				$end_date   = $event_data['end_date'];
			}else{
				$start_date = date('Y-m-d' );
				$end_date   = date('Y-m-d', strtotime('+1 years') );
			}
		}else{
			$start_date = date('Y-m-d' );
			$end_date   = date('Y-m-d', strtotime('+1 years') );
		}
		
		if( isset( $event_data['start_date'] ) && $event_data['start_date'] != '' ){
			$start_date = $event_data['start_date'];
		}
		if( isset( $event_data['end_date'] ) && $event_data['end_date'] != '' ){
 			$end_date = $event_data['end_date'];
		}

		$start_date  = strtotime( $start_date );
		$end_date  = strtotime( $end_date );
		if( ( $end_date - $start_date ) < 0 ){
			$wpea_errors[] = esc_html__( 'Please select end date bigger than start date.', 'wp-event-aggregator');
			return false;
		}
		// Get Start and End date  day,month,year
		$start_month = date( 'm', $start_date );
		$start_year  = date( 'Y', $start_date );
		$start_day   = date( 'd', $start_date );
		$end_month = date( 'm', $end_date );
		$end_year  = date( 'Y', $end_date );
		$end_day   = date( 'd', $end_date );

		// initiate Vcalendar
		//$config = array( 'unique_id' => 'WP_Event_Aggregator_Ical_Parser' . microtime( true ) ); 
		//$calendar = new Vcalendar( $config );
		$calendar = new Vcalendar();
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
						
						$centralize_event = $this->generate_centralize_event_array( $event, $event_data );
						if( !empty( $centralize_event ) ){
							$centralize_events[$centralize_event['ID']] = $centralize_event;
						}
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
	public function generate_centralize_event_array( $event, $event_data = array() ) {
		if( empty( $event ) ){
			return false;
		}
		global $importevents;
		
		$is_recurrence_event = $event->getProperty( 'X-RECURRENCE' );
		$post_title = str_replace('\n', ' ', $event->getProperty( 'SUMMARY' ) );
		$post_description = str_replace('\n', '<br/>', $event->getProperty( 'DESCRIPTION' ) );
		$uid = $this->generate_uid_for_ical_event( $event );
		$uid_old = $this->generate_uid_for_ical_event_old_support( $event );
		$url = $event->getProperty( 'URL' );
		$is_all_day = false;
		
		$system_timezone = date_default_timezone_get();
		$wordpress_timezone = $this->wordpress_timezone();
		$calendar_timezone = $this->timezone;

		$start = $event->getProperty( 'dtstart', 1, true );
		$end   = $event->getProperty( 'dtend',   1, true );

		if ( empty( $end ) ) {
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
				$time_diff += 1;			
			}
			if( $x_time_diff == $time_diff ){
				$start_time = $x_start_time;
				$end_time = $x_end_time;
                if ( $is_all_day ) {
                    $end_time -= 1;
                }
			}		
		}

		$event_image = '';
		$event_venue = null;
		$ical_attachment = $event->getProperty( 'ATTACH', false, true );
		if( isset($ical_attachment['params']) && isset($ical_attachment['params']['FMTTYPE']) ) {
			$attachment_type = $ical_attachment['params']['FMTTYPE'];
			$image_types = array('image/jpeg', 'image/gif', 'image/png', 'image/jpg');
			if( in_array($attachment_type, $image_types ) && !empty($ical_attachment['value']) ){
				$event_image =  $ical_attachment['value'];
			}
		}

		$ical_wp_images = $event->getProperty('X-WP-IMAGES-URL');
		if( !empty( $ical_wp_images ) && !empty( $ical_wp_images[1]) ){
			$event_image =  $ical_wp_images[1];
		}

		$timezone_name = !empty( $timezone ) ? $timezone : $calendar_timezone;

		// Only for facebook ical imports.
		$match = 'https://www.facebook.com/events/';
		if ( strpos( $url, $match ) !== false ) {
			
			$startDateTime = new DateTime( $start );
			$endDateTime   = new DateTime( $end );
			
			$cwt_start     = $this->convert_fb_ical_timezone( $startDateTime->format('Y-m-d H:i:s') );
			$cwt_end       = $this->convert_fb_ical_timezone( $endDateTime->format('Y-m-d H:i:s') );
			$timezone      = $wordpress_timezone;
			$timezone_name = $cwt_start['timezone_name'];
			$start_time    = strtotime( $cwt_start['date_format'] );
			$end_time      = strtotime( $cwt_end['date_format'] );
		}

		$post_description = $importevents->common->wpea_remove_facebook_link_in_event_description( $post_description, $uid );
		$post_description = $importevents->common->wpea_convert_text_to_hyperlink( $post_description );
		
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
			'startime_utc'    => $start_time,
			'endtime_utc'     => $end_time,
			'timezone'        => $timezone,
			'timezone_name'   => $timezone_name,
			'utc_offset'      => '',
			'event_duration'  => '',
			'is_all_day'      => $is_all_day,
			'url'             => $url,
			'image_url'       => $event_image,
		);

		$oraganizer_data = null;
		$event_location = null;

		$organizer = $event->getProperty( 'ORGANIZER', false, true);
		$organizer_params = isset($organizer['params']) ? $organizer['params'] : array();
		$organizer = isset($organizer['value']) ? $organizer['value'] : '';
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
					} else {
						// Check if only email is there.
						$oraganizer = explode( 'MAILTO:', $k);
						if(isset($oraganizer[1]) && !empty($oraganizer[1])){
							$oraganizer_data['ID'] = strtolower( str_replace( ' ','_', trim( preg_replace( '/^"(.*)"$/', '\1', $oraganizer[1] ) ) ) );
							$oraganizer_data['name'] = preg_replace( '/^"(.*)"$/', '\1', $oraganizer[1] );
							$oraganizer_data['email'] = preg_replace( '/^"(.*)"$/', '\1', trim( $oraganizer[1]) );
							if(!empty($organizer_params['CN'])){
								$oraganizer_data['name'] = $organizer_params['CN'];
							}
						}
					}
				}
			}
		}		

		if( $oraganizer_data['email'] == 'noreply@facebookmail_com' ){
			$oraganizer_data['email'] = 'noreply@facebookmail.com';
		}
		
		$xt_event['organizer'] = $oraganizer_data;
		$xt_event['location'] = $this->get_location( $event, $event_venue );
		
		return apply_filters( 'wpea_ical_generate_centralize_array', $xt_event, $event );
	}

	/**
	 * Get location args for event
	 *
	 * @since    1.0.0
	 * @param array $event iCal vevent.
	 * @return array
	 */
	public function get_location( $event, $event_venue ) {
		if ( ! empty( $event_venue ) ) {
			$event_location = array(
				'ID'           => isset( $event_venue->id ) ? $event_venue->id : '',
				'name'         => isset( $event_venue->name ) ? $event_venue->name : '',
				'description'  => '',
				'address_1'    => isset( $event_venue->location->street ) ? $event_venue->location->street : '',
				'address_2'    => '',
				'city'         => isset( $event_venue->location->city ) ? $event_venue->location->city : '',
				'state'        => isset( $event_venue->location->state ) ? $event_venue->location->state : '',
				'country'      => isset( $event_venue->location->country ) ? $event_venue->location->country : '',
				'zip'          => isset( $event_venue->location->zip ) ? $event_venue->location->zip : '',
				'lat'          => isset( $event_venue->location->latitude ) ? $event_venue->location->latitude : '',
				'long'         => isset( $event_venue->location->longitude ) ? $event_venue->location->longitude : '',
				'full_address' => isset( $event_venue->location->street ) ? $event_venue->location->street : '',
				'url'          => '',
				'image_url'    => '',
			);	
			return $event_location;
		}else{
			$geo       = $event->getProperty( 'GEO' );
			$latitude  = isset( $geo['latitude'] ) ? (float)$geo['latitude'] : '';	
			$longitude = isset( $geo['longitude'] ) ? (float)$geo['longitude'] : '';
			$location  = str_replace('\n', ' ', $event->getProperty( 'LOCATION' ) );
			if ( empty( $location ) ) {
				return null;
			}
			if ( !empty( $location ) || !empty( $geo ) ) {
				$event_location = array(
					'ID'           => strtolower( trim( stripslashes( $location ) ) ),
					'name'         => isset( $location ) ? stripslashes( $location ) : '',
					'description'  => '',
					'address_1'    => '',
					'address_2'    => '',
					'city'         => '',
					'state'        => '',
					'country'      => '',
					'zip'	       => '',
					'lat'     	   => $latitude,
					'long'		   => $longitude,
					'full_address' => '',
					'url'          => '',
					'image_url'    => ''
				);
			}
			return $event_location;
		}
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
		if ( is_array( $recurrence_id) ) {
			$recurrence_id = implode('-', $recurrence_id);
		}
		if ( false === $recurrence_id && false !== $ical_event->getProperty( 'X-RECURRENCE' ) ) {
			$current_dt_start = $ical_event->getProperty( 'X-CURRENT-DTSTART' );
			$recurrence_id    = isset( $current_dt_start[1] ) ? $current_dt_start[1] : false;
		}
		$event_id = $ical_event->getProperty( 'UID' ) . $recurrence_id;
		$event_id = explode( '@facebook', $event_id );
		$ical_event_id = str_replace('e', '', $event_id[0]);
		return $ical_event_id;
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
	 * Convert datetime to desired timezone.
	 *
	 * @param string $event_datetime     Date string to possibly convert
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function convert_fb_ical_timezone( $event_datetime = '', $timezone = '' ) {
		
		$timezone_string = get_option( 'timezone_string' );
		$offset  = (float) get_option( 'gmt_offset' );
		if( !empty( $timezone ) ){
			$tz = $timezone;
		}else{

			if ( ! empty( $timezone_string ) ) {
				$tz        = $timezone_string;
			} else {

				$hours     = (int) $offset;
				$minutes   = ( $offset - $hours );
				$sign      = ( $offset < 0 ) ? '-' : '+';
				$abs_hour  = abs( $hours );
				$abs_mins  = abs( $minutes * 60 );
				$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
				
				list($hours, $minutes) = explode(':', $tz_offset);
				$seconds = $hours * 60 * 60 + $minutes * 60;
				$tz = timezone_name_from_abbr('', $seconds, 1);
				if($tz === false) $tz = timezone_name_from_abbr('', $seconds, 0);
			}
		}
		$utc_tz         = new DateTimeZone( 'UTC' );
		$datetime       = new DateTime( $event_datetime, $utc_tz );
		$event_timezone = new DateTimeZone( $tz );
		$datetime->setTimezone( $event_timezone );
		$date_format    = $datetime->format('Y-m-d H:i:s');
		$return_args = array(
			'timezone_name' => $tz,
			'date_format'   => $date_format,
		);
		return $return_args;
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