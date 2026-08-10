<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.Security.EscapeOutput.ExceptionNotEscaped, missing_direct_file_access_protection

/**
 * Class ActionScheduler_Schedule
 */
interface ActionScheduler_Schedule {
	/**
	 * Get the date & time this schedule was created to run, or calculate when it should be run
	 * after a given date & time.
	 *
	 * @param null|DateTime $after Timestamp.
	 * @return DateTime|null
	 */
	public function next( ?DateTime $after = null );

	/**
	 * Identify the schedule as (not) recurring.
	 *
	 * @return bool
	 */
	public function is_recurring();
}
