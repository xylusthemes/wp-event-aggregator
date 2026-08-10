<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.Security.EscapeOutput.ExceptionNotEscaped, missing_direct_file_access_protection

/**
 * Class ActionScheduler_FinishedAction
 */
class ActionScheduler_FinishedAction extends ActionScheduler_Action {

	/**
	 * Execute action.
	 */
	public function execute() {
		// don't execute.
	}

	/**
	 * Get finished state.
	 */
	public function is_finished() {
		return true;
	}
}
