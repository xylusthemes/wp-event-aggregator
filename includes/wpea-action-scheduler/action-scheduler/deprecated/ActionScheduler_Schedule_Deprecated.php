<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.Security.EscapeOutput.ExceptionNotEscaped, missing_direct_file_access_protection

/**
 * Class ActionScheduler_Abstract_Schedule
 */
abstract class ActionScheduler_Schedule_Deprecated implements ActionScheduler_Schedule {

	/**
	 * Get the date & time this schedule was created to run, or calculate when it should be run
	 * after a given date & time.
	 *
	 * @param DateTime $after DateTime to calculate against.
	 *
	 * @return DateTime|null
	 */
	public function next( ?DateTime $after = null ) {
		if ( empty( $after ) ) {
			$return_value       = $this->get_date();
			$replacement_method = 'get_date()';
		} else {
			$return_value       = $this->get_next( $after );
			$replacement_method = 'get_next( $after )';
		}

		_deprecated_function( __METHOD__, '3.0.0', __CLASS__ . '::' . $replacement_method ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return $return_value;
	}
}
