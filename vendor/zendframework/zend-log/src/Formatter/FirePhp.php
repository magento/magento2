<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Formatter;

class FirePhp implements FormatterInterface
{
    /**
     * Formats the given event data into a single line to be written by the writer.
     *
     * @param  array $event The event data which should be formatted.
     * @return array line message and optionally label if 'extra' data exists.
     */
    public function format($event)
    {
        $label = null;
        if (!empty($event['extra'])) {
            $line  = $event['extra'];
            $label = $event['message'];
        } else {
            $line = $event['message'];
        }

        return array($line, $label);
    }

    /**
     * This method is implemented for FormatterInterface but not used.
     *
     * @return string
     */
    public function getDateTimeFormat()
    {
        return '';
    }

    /**
     * This method is implemented for FormatterInterface but not used.
     *
     * @param  string             $dateTimeFormat
     * @return FormatterInterface
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        return $this;
    }
}
