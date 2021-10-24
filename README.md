# WP Event Aggregator

> WP Event Aggregator: Easy way to import Facebook Events, Eventbrite events, MeetUp events into your WordPress Event Calendar.

[![Release Version](https://img.shields.io/github/v/release/xylusthemes/wp-event-aggregator.svg)](https://github.com/xylusthemes/wp-event-aggregator/releases/latest)
![WordPress tested up to version](https://img.shields.io/badge/WordPress-v5.8%20tested-success.svg)
[![GPLv2.0 License](https://img.shields.io/github/license/xylusthemes/wp-event-aggregator.svg)](https://github.com/xylusthemes/wp-event-aggregator/blob/master/LICENSE.txt)

## Description
WP Event Aggregator ([Pro]) allows you to import Events from anywhere - Facebook, Eventbrite, Meetup, iCalendar and ICS into your WordPress site. You can import Facebook Events, Eventbrite Events, Meetup events and other iCAL supported events into WordPress. 

WP Event Aggregator works as a stand-alone as well as add-on plugin with leading Event Calendar plugins listed below, which allows you to run imports from multiple sources right from your dashboard, including Facebook, Meetup, Google Calendar( using iCal Url or .ics file ), Outlook Calendar ( using iCal Url or .ics file ), Apple Calendar ( using iCal Url or .ics file ), iCalendar, and ICS into WordPress.  WP Event aggregator is a perfect match for Event, Event Directory, City Directory, Hotel or School website who needs to display events. 

**Import Events from following sources**

* Facebook Events (Using Facebook API)
* Eventbrite Events (Using Eventbrite API)
* Meetup Events (Using MeetUp API) 
* iCalendar
* ICS
* Google Calendar ( using iCal Url or .ics file )
* Outlook Calendar ( using iCal Url or .ics file )
* Apple Calendar ( using iCal Url or .ics file )

**Import Events into following plugins**

* [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/)
* [Events manager](https://wordpress.org/plugins/events-manager/)
* [All-in-One Event Calendar](https://wordpress.org/plugins/all-in-one-event-calendar/)
* [Event Organiser](https://wordpress.org/plugins/event-organiser/)
* [EventON](https://codecanyon.net/item/eventon-wordpress-event-calendar-plugin/1211017)
* [My Calendar](https://wordpress.org/plugins/my-calendar/)
* [Eventum (Tevolution-Events)](https://templatic.com/app-themes/eventum-event-directory-theme/) - Pro Add-on
* [Event Espresso 4 (EE4)](https://wordpress.org/plugins/event-espresso-decaf/)
* GeoDirectory Events - [Addon](https://xylusthemes.com/plugins/wpea-geodirectory-events-addon/)
* AIT Events (AIT Themes) - [Addon](https://xylusthemes.com/plugins/wpea-ait-events-addon/)
* In-built Events Management.


**Features**

* Easy and seemless Event import from Facebook, Eventbrite, Meetup, iCalendar and ICS to WordPres.
* Import Eventrite event by Event ID, organiser ID ([Pro]) and from your Eventbrite account ([Pro]).
* Import Facebook event by Event ID, Facebook page ID ([Pro]), Facebook group ([Pro]).
* Import Meetup events by meetup group URL.
* Import iCal events using .ics file upload or by iCal URL([Pro]).
* Import multiple events using multiple event IDs at one time ([Pro]).
* Import events into all WordPress leading Events plugins.
* Scheduled event import (Automatic import) from above sources and import events effortlessly using import frequency like Hourly, Once a Day, Twice a day, Weekly, Monthly([Pro]).
* Simple and Effective in-built Event Management.
* Powerful shortcode for render Event listing (`[wp_events]`)
* Responsive and impresive event listing design
* Impresive design of event detail page.
* Upcoming Events widget ([Pro]).
* It support One Time Import only or schedule import on regular interval. 
* Each Event Import can be imported in different categories.
* Auto Publish, Draft Imported Events 
* Get Event details like Event Title, Event Description, Event Images, Event Start Date, Event End date, Event Location (vanue), Event Organizer etc in to WordPress Database.
* Event Import History Logs when & which Events Imported
* Option to update existing Events (Syncronize Events)
* Works with leading WordPress Event Calendar Plug-ins
* Works with WPBackery Page Builder. Support for more page builders is on the way :)

[Try Now (Admin Demo)](http://testdrive.xylusthemes.com/)

[Documentation](http://docs.xylusthemes.com/docs/wp-event-aggregator/)

[Pro] Add-on



[Pro]: https://xylusthemes.com/plugins/wp-event-aggregator/?utm_source=githubrepo&utm_campaign=wpaggregator&utm_medium=readme&utm_content=githubrepo-readme


You can use `[wp_events]` for display in-built events list.

**Shortcode Examples:** 
`[wp_events]`
`[wp_events col="3" posts_per_page="12"]`
`[wp_events category="cat1,cat2"]`
`[wp_events col="2" posts_per_page="12" category="cat1,cat2" past_events="yes" order="desc" orderby="post_title" start_date="2017-12-25" end_date="2018-12-25" ]`

### NOTICE (FOR FACEBOOK IMPORT ONLY):
>**You need below things to work Facebook Event Importing using API.**
>
>* Facebook app ([Here](http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/) is how to create FB app)
>* Your site need to HTTPS (SSL certificate)
>* You need to mark events as interested or going on facebook to get imported
>
>**You can also import Facebook events using our plugin with facebook ICS URL. You can check the documentation for how to get iCal URL or download .ics file from Facebook at [Here](http://docs.xylusthemes.com/docs/import-facebook-events/how-to-import-facebook-event-using-ical-url/)**
>

## Changelog

A complete listing of all notable changes to "WP Event Aggregator" are documented in [CHANGELOG.md](https://github.com/xylusthemes/wp-event-aggregator/blob/develop/CHANGELOG.md).
