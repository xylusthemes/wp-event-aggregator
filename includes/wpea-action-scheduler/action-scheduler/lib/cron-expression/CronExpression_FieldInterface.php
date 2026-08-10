<?php
// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedInterfaceFound, WordPress.Security.EscapeOutput.ExceptionNotEscaped, missing_direct_file_access_protection

/**
 * CRON field interface
 *
 * @author Michael Dowling <mtdowling@gmail.com>
 */
interface CronExpression_FieldInterface
{
    /**
     * Check if the respective value of a DateTime field satisfies a CRON exp
     *
     * @param DateTime $date  DateTime object to check
     * @param string   $value CRON expression to test against
     *
     * @return bool Returns TRUE if satisfied, FALSE otherwise
     */
    public function isSatisfiedBy(DateTime $date, $value);

    /**
     * When a CRON expression is not satisfied, this method is used to increment
     * or decrement a DateTime object by the unit of the cron field
     *
     * @param DateTime $date   DateTime object to change
     * @param bool     $invert (optional) Set to TRUE to decrement
     *
     * @return CronExpression_FieldInterface
     */
    public function increment(DateTime $date, $invert = false);

    /**
     * Validates a CRON expression for a given field
     *
     * @param string $value CRON expression value to validate
     *
     * @return bool Returns TRUE if valid, FALSE otherwise
     */
    public function validate($value);
}
