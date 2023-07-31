<?php
/**
  * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator\Util;

use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Vcalendar;

use function ctype_digit;
use function floor;
use function in_array;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;

/**
 * iCalcreator DateTimeZone support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.27.8 - 2019-01-12
 */
class DateTimeZoneFactory
{

    /**
     * @var array
     * @static
     */
    public static $UTCARR = [ 'Z', Vcalendar::UTC, Vcalendar::GMT ];

    /**
     * Return new DateTimeZone object instance
     *
     * @param string $tzString
     * @return DateTimeZone
     * @throws InvalidArgumentException
     * @static
     * @since  2.27.8 - 2019-01-12
     */
    public static function factory( $tzString = null )
    {
        return self::assertDateTimeZone( $tzString );
    }

    /**
     * Assert DateTimeZoneString
     *
     * @param string $tzString
     * @return DateTimeZone
     * @throws InvalidArgumentException
     * @static
     * @since  2.27.14 - 2019-01-31
     */
    public static function assertDateTimeZone( $tzString )
    {
        static $ERR = 'Invalid DateTimeZone \'%s\'';
        if( empty( $tzString ) && ( 0 != intval( $tzString ))) {
            throw new InvalidArgumentException( sprintf( $ERR, $tzString ));
        }
        if( self::hasOffset( $tzString )) {
            $tzString = self::getTimeZoneNameFromOffset( $tzString );
        }
        elseif( in_array( $tzString, self::$UTCARR )) {
            $tzString = Vcalendar::UTC;
        }
        try {
            $tzString = self::standard_to_name( $tzString );
            $timeZone = new DateTimeZone( $tzString );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException( sprintf( $ERR, $tzString ), null, $e );
        }
        return $timeZone;
    }

    /**
     * standard timezone name converter
     */
    public static function standard_to_name( $tzString ){
        $microsoftWindowsTimeZones = [
            'Dateline Standard Time'          => 'Etc/GMT+12',
            'Aleutian Standard Time'          => 'America/Adak',
            'Hawaiian Standard Time'          => 'Etc/GMT+10',
            'Marquesas Standard Time'         => 'Pacific/Marquesas',
            'Alaskan Standard Time'           => 'America/Anchorage',
            'Pacific Standard Time (Mexico)'  => 'America/Tijuana',
            'Pacific Standard Time'           => 'US/Pacific',
            'US Mountain Standard Time'       => 'Etc/GMT+7',
            'Mountain Standard Time (Mexico)' => 'America/Chihuahua',
            'Mountain Standard Time'          => 'America/Boise',
            'Central America Standard Time'   => 'Etc/GMT+6',
            'Central Standard Time'           => 'America/Chicago',
            'Easter Island Standard Time'     => 'Pacific/Easter',
            'Central Standard Time (Mexico)'  => 'America/Mexico_City',
            'Canada Central Standard Time'    => 'America/Regina',
            'SA Pacific Standard Time'        => 'Etc/GMT+5',
            'Eastern Standard Time (Mexico)'  => 'America/Cancun',
            'Eastern Standard Time'           => 'US/Eastern',
            'Haiti Standard Time'             => 'America/Port-au-Prince',
            'Cuba Standard Time'              => 'America/Havana',
            'US Eastern Standard Time'        => 'America/Indianapolis',
            'Paraguay Standard Time'          => 'America/Asuncion',
            'Atlantic Standard Time'          => 'America/Thule',
            'Venezuela Standard Time'         => 'America/Caracas',
            'Central Brazilian Standard Time' => 'America/Cuiaba',
            'SA Western Standard Time'        => 'Etc/GMT+4',
            'Pacific SA Standard Time'        => 'America/Santiago',
            'Turks And Caicos Standard Time'  => 'America/Grand_Turk',
            'Newfoundland Standard Time'      => 'America/St_Johns',
            'Tocantins Standard Time'         => 'America/Araguaina',
            'E. South America Standard Time'  => 'America/Sao_Paulo',
            'SA Eastern Standard Time'        => 'Etc/GMT+3',
            'Argentina Standard Time'         => 'America/Buenos_Aires',
            'Greenland Standard Time'         => 'America/Godthab',
            'Montevideo Standard Time'        => 'America/Montevideo',
            'Magallanes Standard Time'        => 'America/Punta_Arenas',
            'Saint Pierre Standard Time'      => 'America/Miquelon',
            'Bahia Standard Time'             => 'America/Bahia',
            'Azores Standard Time'            => 'Atlantic/Azores',
            'Cape Verde Standard Time'        => 'GMT+1',
            'GMT Standard Time'               => 'UTC',
            'Greenwich Standard Time'         => 'Africa/Lome',
            'W. Europe Standard Time'         => 'Europe/Vatican',
            'Central Europe Standard Time'    => 'Europe/Bratislava',
            'Romance Standard Time'           => 'Europe/Paris',
            'Morocco Standard Time'           => 'Africa/Casablanca',
            'Sao Tome Standard Time'          => 'Africa/Sao_Tome',
            'Central European Standard Time'  => 'Europe/Warsaw',
            'W. Central Africa Standard Time' => 'Etc/GMT-1',
            'Jordan Standard Time'            => 'Asia/Amman',
            'GTB Standard Time'               => 'Europe/Bucharest',
            'Middle East Standard Time'       => 'Asia/Beirut',
            'Egypt Standard Time'             => 'Africa/Cairo',
            'E. Europe Standard Time'         => 'Europe/Chisinau',
            'Syria Standard Time'             => 'Asia/Damascus',
            'West Bank Standard Time'         => 'Asia/Gaza',
            'South Africa Standard Time'      => 'Etc/GMT-2',
            'FLE Standard Time'               => 'Europe/Kiev',
            'Israel Standard Time'            => 'Asia/Jerusalem',
            'Kaliningrad Standard Time'       => 'Europe/Kaliningrad',
            'Sudan Standard Time'             => 'Africa/Khartoum',
            'Libya Standard Time'             => 'Africa/Tripoli',
            'Namibia Standard Time'           => 'Africa/Windhoek',
            'Arabic Standard Time'            => 'Asia/Baghdad',
            'Turkey Standard Time'            => 'Europe/Istanbul',
            'Arab Standard Time'              => 'Asia/Aden',
            'Belarus Standard Time'           => 'Europe/Minsk',
            'Russian Standard Time'           => 'Europe/Simferopol',
            'E. Africa Standard Time'         => 'Etc/GMT-3',
            'Iran Standard Time'              => 'Asia/Tehran',
            'Arabian Standard Time'           => 'Etc/GMT-4',
            'Astrakhan Standard Time'         => 'Europe/Astrakhan',
            'Azerbaijan Standard Time'        => 'Asia/Baku',
            'Russia Time Zone 3'              => 'Europe/Samara',
            'Mauritius Standard Time'         => 'Indian/Mahe',
            'Saratov Standard Time'           => 'Europe/Saratov',
            'Georgian Standard Time'          => 'Asia/Tbilisi',
            'Caucasus Standard Time'          => 'Asia/Yerevan',
            'Afghanistan Standard Time'       => 'Asia/Kabul',
            'West Asia Standard Time'         => 'Etc/GMT-5',
            'Ekaterinburg Standard Time'      => 'Asia/Yekaterinburg',
            'Pakistan Standard Time'          => 'Asia/Karachi',
            'India Standard Time'             => 'Asia/Calcutta',
            'Sri Lanka Standard Time'         => 'Asia/Colombo',
            'Nepal Standard Time'             => 'Asia/Katmandu',
            'Central Asia Standard Time'      => 'Etc/GMT-6',
            'Bangladesh Standard Time'        => 'Asia/Thimphu',
            'Omsk Standard Time'              => 'Asia/Omsk',
            'Myanmar Standard Time'           => 'Asia/Rangoon',
            'SE Asia Standard Time'           => 'Etc/GMT-7',
            'Altai Standard Time'             => 'Asia/Barnaul',
            'W. Mongolia Standard Time'       => 'Asia/Hovd',
            'North Asia Standard Time'        => 'Asia/Krasnoyarsk',
            'N. Central Asia Standard Time'   => 'Asia/Novosibirsk',
            'Tomsk Standard Time'             => 'Asia/Tomsk',
            'China Standard Time'             => 'Asia/Macau',
            'North Asia East Standard Time'   => 'Asia/Irkutsk',
            'Singapore Standard Time'         => 'Etc/GMT-8',
            'W. Australia Standard Time'      => 'Australia/Perth',
            'Taipei Standard Time'            => 'Asia/Taipei',
            'Ulaanbaatar Standard Time'       => 'Asia/Ulaanbaatar',
            'Aus Central W. Standard Time'    => 'Australia/Eucla',
            'Transbaikal Standard Time'       => 'Asia/Chita',
            'Tokyo Standard Time'             => 'Etc/GMT-9',
            'North Korea Standard Time'       => 'Asia/Pyongyang',
            'Korea Standard Time'             => 'Asia/Seoul',
            'Yakutsk Standard Time'           => 'Asia/Yakutsk',
            'Cen. Australia Standard Time'    => 'Australia/Adelaide',
            'AUS Central Standard Time'       => 'Australia/Darwin',
            'E. Australia Standard Time'      => 'Australia/Brisbane',
            'AUS Eastern Standard Time'       => 'Australia/Sydney',
            'West Pacific Standard Time'      => 'Etc/GMT-10',
            'Tasmania Standard Time'          => 'Australia/Hobart',
            'Vladivostok Standard Time'       => 'Asia/Vladivostok',
            'Lord Howe Standard Time'         => 'Australia/Lord_Howe',
            'Bougainville Standard Time'      => 'Pacific/Bougainville',
            'Russia Time Zone 10'             => 'Asia/Srednekolymsk',
            'Magadan Standard Time'           => 'Asia/Magadan',
            'Norfolk Standard Time'           => 'Pacific/Norfolk',
            'Sakhalin Standard Time'          => 'Asia/Sakhalin',
            'Central Pacific Standard Time'   => 'Etc/GMT-11',
            'Russia Time Zone 11'             => 'Asia/Kamchatka',
            'New Zealand Standard Time'       => 'Pacific/Auckland',
            'Fiji Standard Time'              => 'Pacific/Fiji',
            'Chatham Islands Standard Time'   => 'Pacific/Chatham',
            'Tonga Standard Time'             => 'Pacific/Tongatapu',
            'Samoa Standard Time'             => 'Pacific/Apia',
            'Line Islands Standard Time'      => 'Etc/GMT-14'
        ];
        if( !array_key_exists( $tzString, $microsoftWindowsTimeZones ) ){
            return $tzString;
        }
        
        return $microsoftWindowsTimeZones[$tzString];
    }

    /**
     * Return (array) all transitions from timezone
     *
     * @param string|DateTimeZone $dateTimeZone
     * @param int $from
     * @param int $to
     * @return array
     * @throws InvalidArgumentException
     * @static
     * @since  2.27.8 - 2019-01-22
     */
    public static function getDateTimeZoneTransitions(
        $dateTimeZone,
        $from = null,
        $to = null
    ) {
        if( ! $dateTimeZone instanceof DateTimeZone ) {
            $dateTimeZone = self::factory( $dateTimeZone );
        }
        $res = $dateTimeZone->getTransitions( $from, $to );
        return ( empty( $res )) ? [] : $res;
    }

    /**
     * Return (first found) timezone from offset
     *
     * @param string $offset
     * @return string
     * @throws InvalidArgumentException
     * @static
     * @since  2.27.14 - 2019-02-26
     */
    public static function getTimeZoneNameFromOffset( $offset )
    {
        static $UTCOFFSET = '+00:00';
        static $ERR       = 'Offset \'%s\' (%+d seconds) don\'t match any timezone';
        if( $UTCOFFSET  == $offset ) {
            return self::$UTCARR[1];
        }
        $seconds = self::offsetToSeconds( $offset );
        $res     =  timezone_name_from_abbr( Util::$SP0, $seconds );
        if( false === $res ) {
            $res = timezone_name_from_abbr( Util::$SP0, $seconds, 0 );
        }
        if( false === $res ) {
            $res = timezone_name_from_abbr( Util::$SP0, $seconds, 1 );
        }
        if( false === $res ) {
            throw new InvalidArgumentException( sprintf( $ERR, $offset, $seconds ));
        }
        return $res;
    }

    /**
     * Return offset part from dateString
     *
     * An offset is one of [+/-]NNNN, [+/-]NN:NN, [+/-]NNNNNN, [+/-]NN:NN:NN
     * @param string $dateString
     * @return string
     * @static
0     */
    public static function getOffset( $dateString )
    {
        $dateString = trim( $dateString );
        $ix         = strlen( $dateString ) - 1;
        $offset     = null;
        while( true ) {
            $dateX1 = substr( $dateString, $ix, 1 );
            switch( true ) {
                case ctype_digit( $dateX1 ) :
                    $offset = $dateX1 . $offset;
                    break;
                case ( Util::$COLON == $dateX1 ) :
                    $offset = $dateX1 . $offset;
                    break;
                case DateIntervalFactory::hasPlusMinusPrefix( $dateX1 ) :
                    $offset = $dateX1 . $offset;
                    break 2;
                default :
                    $offset = null;
                    break 2;
            } // end switch
            if( 1 > $ix ) {
                break;
            }
            $ix -= 1;
        } // end while
        return $offset;
    }

    /**
     * Return bool true if input string contains (trailing) UTC/iCal offset
     *
     * An offset is one of [+/-]NNNN, [+/-]NN:NN, [+/-]NNNNNN, [+/-]NN:NN:NN
     * @param string $string
     * @return bool
     * @static
     * @since  2.27.14 - 2019-02-18
     */
    public static function hasOffset( $string )
    {
        $string = trim((string) $string );
        if( empty( $string )) {
            return false;
        }
        if( Vcalendar::Z == substr( $string, -1 )) {
            return false;
        }
        if( false != strpos( $string, Util::$COLON )) {
            $string = str_replace( Util::$COLON, Util::$SP0, $string );
        }
        if( DateIntervalFactory::hasPlusMinusPrefix( substr( $string, -5 )) &&
            ctype_digit( substr( $string, -4 ))) {
            return true;
        }
        if( DateIntervalFactory::hasPlusMinusPrefix( substr( $string, -7 )) &&
            ctype_digit( substr( $string, -6 ))) {
            return true;
        }
        return false;
    }

    /**
     * Return bool true if UTC timezone
     *
     * @param string $timeZoneString
     * @return bool
     * @static
     * @since  2.27.8 - 2019-01-21
     */
    public static function isUTCtimeZone( $timeZoneString )
    {
        if( empty( $timeZoneString )) {
            return false;
        }
        if( self::hasOffset( $timeZoneString )) {
            if( false !== strpos( $timeZoneString, Util::$COLON )) {
                $timeZoneString = str_replace( Util::$COLON, null, $timeZoneString );
            }
            return ( empty( intval( $timeZoneString, 10 )));
        }
        return ( in_array( strtoupper( $timeZoneString ), self::$UTCARR ));
    }

    /**
     * Return seconds based on an offset, [+/-]HHmm[ss], used when correcting UTC to localtime or v.v.
     *
     * @param string $offset
     * @return string
     * @static
     * @since  2.26.7 - 2018-11-23
     */
    public static function offsetToSeconds( $offset )
    {
        $offset  = trim( (string) $offset );
        $seconds = 0;
        if( false !== strpos( $offset, Util::$COLON )) {
            $offset = str_replace( Util::$COLON, null, $offset );
        }
        $strLen = strlen( $offset );
        if( ( 5 > $strLen ) || ( 7 < $strLen )) {
            return $seconds;
        }
        if( ! DateIntervalFactory::hasPlusMinusPrefix( $offset )) {
            return $seconds;
        }
        $isMinus = ( Util::$MINUS == substr( $offset, 0, 1 ));
        if( ! ctype_digit( substr( $offset, 1 ))) {
            return $seconds;
        }
        $seconds += ((int) substr( $offset, 1, 2 )) * 3600;
        $seconds += ((int) substr( $offset, 3, 2 )) * 60;
        if( 7 == $strLen ) {
            $seconds += (int) substr( $offset, 5, 2 );
        }
        return ( $isMinus ) ? $seconds * -1 : $seconds;
    }

    /**
     * Return iCal offset [-/+]hhmm[ss] (string) from UTC offset seconds
     *
     * @param string $offset
     * @return string
     * @static
     * @since  2.26 - 2018-11-10
     */
    public static function secondsToOffset( $offset )
    {
        static $FMT = '%02d';
        switch( substr( $offset, 0, 1 )) {
            case Util::$MINUS :
                $output = Util::$MINUS;
                $offset = substr( $offset, 1 );
                break;
            case Util::$PLUS :
                $output = Util::$PLUS;
                $offset = substr( $offset, 1 );
                break;
            default :
                $output = Util::$PLUS;
                break;
        } // end switch
        $output .= sprintf( $FMT, ((int) floor( $offset / 3600 ))); // hour
        $seconds = $offset % 3600;
        $output .= sprintf( $FMT, ((int) floor( $seconds / 60 )));   // min
        $seconds = $seconds % 60;
        if( 0 < $seconds ) {
            $output .= sprintf( $FMT, $seconds ); // sec
        }
        return $output;
    }
}
