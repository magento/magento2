<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Formatter;

use Traversable;
use Zend\Log\Exception;

class Simple extends Base
{
    const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%): %message% %extra%';

    /**
     * Format specifier for log messages
     *
     * @var string
     */
    protected $format;

    /**
     * Class constructor
     *
     * @see http://php.net/manual/en/function.date.php
     * @param null|string $format Format specifier for log messages
     * @param null|string $dateTimeFormat Format specifier for DateTime objects in event data
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($format = null, $dateTimeFormat = null)
    {
        if ($format instanceof Traversable) {
            $format = iterator_to_array($format);
        }

        if (is_array($format)) {
            $dateTimeFormat = isset($format['dateTimeFormat'])? $format['dateTimeFormat'] : null;
            $format         = isset($format['format'])? $format['format'] : null;
        }

        if (isset($format) && !is_string($format)) {
            throw new Exception\InvalidArgumentException('Format must be a string');
        }

        $this->format = isset($format) ? $format : static::DEFAULT_FORMAT;

        parent::__construct($dateTimeFormat);
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param array $event event data
     * @return string formatted line to write to the log
     */
    public function format($event)
    {
        $output = $this->format;

        $event = parent::format($event);
        foreach ($event as $name => $value) {
            if ('extra' == $name && count($value)) {
                $value = $this->normalize($value);
            } elseif ('extra' == $name) {
                // Don't print an empty array
                $value = '';
            }
            $output = str_replace("%$name%", $value, $output);
        }

        if (isset($event['extra']) && empty($event['extra'])
            && false !== strpos($this->format, '%extra%')
        ) {
            $output = rtrim($output, ' ');
        }
        return $output;
    }
}
