<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.Security.EscapeOutput.ExceptionNotEscaped, missing_direct_file_access_protection

/**
 * Class ActionScheduler_NullLogEntry
 */
class ActionScheduler_NullLogEntry extends ActionScheduler_LogEntry {

	/**
	 * Construct.
	 *
	 * @param string $action_id Action ID.
	 * @param string $message   Log entry.
	 */
	public function __construct( $action_id = '', $message = '' ) {
		// nothing to see here.
	}

}
