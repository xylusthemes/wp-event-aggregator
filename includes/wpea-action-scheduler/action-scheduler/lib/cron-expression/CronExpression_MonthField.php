<?php
// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound, WordPress.Security.EscapeOutput.ExceptionNotEscaped, missing_direct_file_access_protection

/**
 * Month field.  Allows: * , / -
 *
 * @author Michael Dowling <mtdowling@gmail.com>
 */
class CronExpression_MonthField extends CronExpression_AbstractField
{
    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(DateTime $date, $value)
    {
        // Convert text month values to integers
        $value = str_ireplace(
            array(
                'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
                'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'
            ),
            range(1, 12),
            $value
        );

        return $this->isSatisfied($date->format('m'), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function increment(DateTime $date, $invert = false)
    {
        if ($invert) {
            // $date->modify('last day of previous month'); // remove for php 5.2 compat
            $date->modify('previous month');
            $date->modify($date->format('Y-m-t'));
            $date->setTime(23, 59);
        } else {
            //$date->modify('first day of next month'); // remove for php 5.2 compat
            $date->modify('next month');
            $date->modify($date->format('Y-m-01'));
            $date->setTime(0, 0);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        return (bool) preg_match('/[\*,\/\-0-9A-Z]+/', $value);
    }
}
