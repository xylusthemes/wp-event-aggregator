=== WP Event Aggregator: Import Facebook, Eventbrite, Meetup, iCal Events ===
Contributors: xylus,dharm1025
Donate link: http://xylusthemes.com
Tags: event, aggregator, import, iCal, google, facebook, eventbrite, meetup, event aggregator, event feeds, event bulk import,  import events, event import, wp events calendar, wp event,event import, event directory, events manager, the events calendar, events, import events, facebook event, meetup event, eventbrite event, ical import, ics import, event management, event calendar, event manager
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Import Events from anywhere - Facebook, Eventbrite, Meetup, iCalendar and ICS into your WordPress site.

== Description ==

WP Event Aggregator allows you to import Events from anywhere - Facebook, Eventbrite, Meetup, iCalendar and ICS into your WordPress site.

[Try Now (Admin Demo)](http://testdrive.xylusthemes.com/) | [Documentation](http://docs.xylusthemes.com/docs/wp-event-aggregator/) | [Pro Version][1]

This Plugin is works as a stand alone as well as add-on plugin for below listed Events plugins, which allows you to run imports from multiple sources right from your dashboard, including Facebook, Meetup, Google Calendar( using iCal Url or .ics file ), Outlook Calendar ( using iCal Url or .ics file ), Apple Calendar ( using iCal Url or .ics file ), iCalendar, and ICS into WordPress. As a stand alone plugin this plugin work independently for event import and Event Management.

**Import Events from following sources**

* Facebook
* Eventbrite
* Meetup
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
* [Eventum (Tevolution-Events)](https://templatic.com/app-themes/eventum-event-directory-theme/) - Pro Version
* In-built Events Management.


**Features**

* Easy and seemless Event import from Facebook, Eventbrite, Meetup, iCalendar and ICS to WordPres.
* Import Eventrite event by Event ID, organiser ID ([Pro][1]) and from your Eventbrite account ([Pro][1]).
* Import Facebook event by Event ID, Facebook page ID ([Pro][1]), Facebook group ([Pro][1]).
* Import Meetup events by meetup group URL.
* Import iCal events using .ics file upload or by iCal URL([Pro][1]).
* Import multiple events using multiple event IDs at one time ([Pro][1]).
* Import events into all WordPress leading Events plugins.
* Scheduled event import (Automatic import) from above sources and import events effortlessly using import frequency like Hourly, Once a Day, Twice a day, Weekly, Monthly([Pro][1]).
* Simple and Effective in-built Event Management.
* Powerful shortcode for render Event listing (`[wp_events]`)
* Responsive and impresive event listing design
* Impresive design of event detail page.
* Upcoming Events widget ([Pro][1]).
* It support One Time Import only or schedule import on regular interval. 
* Each Event Import can be imported in different categories.
* Auto Publish, Draft Imported Events 
* Get Event details like Event Title, Event Description, Event Images, Event Start Date, Event End date, Event Location (vanue), Event Organizer etc in to WordPress Database.
* Event Import History Logs when & which Events Imported
* Option to update existing Events (Syncronize Events)
* Works with leading WordPress Event Calendar Plug-ins

[1]: https://xylusthemes.com/plugins/wp-event-aggregator/?utm_source=wprepo&utm_campaign=wpaggregator&utm_medium=readme&utm_content=wprepo-readme


You can use `[wp_events]` for display in-built events list.

<strong>Shortcode Examples:</strong> 
`[wp_events]`
`[wp_events col="3" posts_per_page="12"]`
`[wp_events category="cat1,cat2"]`
`[wp_events col="2" posts_per_page="12" category="cat1,cat2" past_events="yes" order="desc" orderby="post_title" start_date="2017-12-25" end_date="2018-12-25" ]`

== Installation ==

**Installation (Free)**

<strong>This plugin can be installed directly from your site.</strong>

1. Log in and navigate to Plugins & Add New.
2. Type "WP Event Aggregator" into the Search input and click the "Search" button.
3. Locate the "WP Event Aggregator" in the list of search results and click "Install Now".
4. Click the "Activate Plugin" link at the bottom of the install screen.

<strong>It can also be installed manually.</strong>

1. Download the "WP Event Aggregator" plugin from WordPress.org.
2. Unzip the package and move to your plugins directory.
3. Log into WordPress and navigate to the "Plugins" screen.
4. Locate "WP Event Aggregator" in the list and click the "Activate" link.

**Installation (Pro)**

1. Remove basic version of this plugin if you have installed.
2. Download the “WP Event Aggregator Pro” plugin from your profile on [https://xylusthemes.com/](https://xylusthemes.com/).
3. Log in and navigate to Plugins & Add New.
4. Click on Upload plugin button and upload “WP Event Aggregator Pro” zip file and click on install now button.
5. Locate “WP Event Aggregator Pro” in the list and click the “Activate” link.

[More Information](http://docs.xylusthemes.com/docs/wp-event-aggregator/plugin-installation-pro/)


== Screenshots ==

1. Events page using '[wp_events posts_per_page="12"]' shortcode
2. Single Event page (Twenty Sixteen Theme).
3. Eventbrite event import using Event ID.
4. Eventbrite event import using Organizer ID (Pro).
5. Scheduled Imports for Eventbrite (Pro).
6. Meetup Event import.
7. Facebook event import using Event IDs.
8. Facebook event import using Page or Organizer ID (Pro).
9. iCal events import.
10. Scheduled Imports (Pro)
11. Import History
12. Settings for import events.
13. Upcoming WP Events widget in backend (Pro)
14. Upcoming WP Events widget in front-end with Event image(Pro)
15. Upcoming WP Events widget in front-end without Event image(Pro)

== Changelog ==

= 1.3.0 =
* ADDED: Support for import events from Facebook group. (Pro)
* ADDED: Now user can import facebook events which are accesible from user’s profile (Pro)
* ADDED: Facebook Authorization option for import facebook group events (Pro)
* ADDED: User can edit scheduled import now. (Pro)
* FIXED: jQuery UI css conflict with DIVI theme
* FIXED: Ical parser issues.
* FIXED: some bug fixes.

= 1.2.4 =
* ADDED: Option for delete data on plugin uninstall
* ADDED: Option for disable inbuilt event managent (WP Events).
* ADDED: Past Events display by add ‘past_events="yes"‘ into shortcode.
* ADDED: ‘col’ attribute into shortcode for number of column layout setup.
* ADDED: New iCal parse library to prevent various issues.
* ADDED: more options in shortcode full shortcode is now like. [wp_events col="2" posts_per_page="12" category="cat1,cat2" past_events="yes" order="desc" orderby="post_title" start_date="2017-12-25" end_date="2018-12-25" ]
* ADDED: Event Type 2 multi select for EventON(Pro).
* ADDED: Multiple event IDs are now insertable at once in Eventbrite import(Pro).
* ADDED: Upcoming Events widget(Pro).
* ADDED: import into Eventum (Tevolution-Events) support(Pro).
* IMPROVEMENTS: In event archive and single event details page.
* IMPROVEMENTS: Make Date multilingual.
* IMPROVEMENTS: Schduled import section has now more information(Pro).
* IMPROVEMENTS: City, State and Country fields mapping to new version of EventON.
* FIXED: jQuery UI css conflict some plugin
* FIXED: TimeZone issue in “All in one Event Calendar” sometime imports wrong eventtime
* FIXED: Ical parser issue for multiday events.
* FIXED: Fixed Image download issue happen on some servers.
* FIXED: some bug fixes
* Version bumed to 1.2.4 for make is same as Pro version.

= 1.1.3 =
* Fixes: some bug fixes

= 1.1.2 =
* FIXED: some bug fixes in events manage.

= 1.1.1 =
* Fixes: some bug fixes

= 1.1.0 =
* Added: in-built event management system.
* Added: import into Events Manager
* Added: Import into My Calendar
* Added: Import into eventON
* Added: import into All-in-One Event Calendar
* Added: import into Event Organizer
* Added: Import history
* Improvements in scheduled imports
* Fixes: some bug fixes

= 1.0.0 =
* Initial Version.
