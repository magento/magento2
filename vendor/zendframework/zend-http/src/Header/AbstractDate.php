<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

use DateTime;
use DateTimeZone;

/**
 * Abstract Date/Time Header
 * Supports headers that have date/time as value
 *
 * @see Zend\Http\Header\Date
 * @see Zend\Http\Header\Expires
 * @see Zend\Http\Header\IfModifiedSince
 * @see Zend\Http\Header\IfUnmodifiedSince
 * @see Zend\Http\Header\LastModified
 *
 * Note for 'Location' header:
 * While RFC 1945 requires an absolute URI, most of the browsers also support relative URI
 * This class allows relative URIs, and let user retrieve URI instance if strict validation needed
 */
abstract class AbstractDate implements HeaderInterface
{
    /**
     * Date formats according to RFC 2616
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.3
     */
    const DATE_RFC1123 = 0;
    const DATE_RFC1036 = 1;
    const DATE_ANSIC   = 2;

    /**
     * Date instance for this header
     *
     * @var DateTime
     */
    protected $date = null;

    /**
     * Date output format
     *
     * @var string
     */
    protected static $dateFormat = 'D, d M Y H:i:s \G\M\T';

    /**
     * Date formats defined by RFC 2616. RFC 1123 date is required
     * RFC 1036 and ANSI C formats are provided for compatibility with old servers/clients
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.3
     *
     * @var array
     */
    protected static $dateFormats = array(
        self::DATE_RFC1123 => 'D, d M Y H:i:s \G\M\T',
        self::DATE_RFC1036 => 'D, d M y H:i:s \G\M\T',
        self::DATE_ANSIC   => 'D M j H:i:s Y',
    );

    /**
     * Create date-based header from string
     *
     * @param string $headerLine
     * @return AbstractDate
     * @throws Exception\InvalidArgumentException
     */
    public static function fromString($headerLine)
    {
        $dateHeader = new static();

        list($name, $date) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== strtolower($dateHeader->getFieldName())) {
            throw new Exception\InvalidArgumentException(
                'Invalid header line for "' . $dateHeader->getFieldName() . '" header string'
            );
        }

        $dateHeader->setDate($date);

        return $dateHeader;
    }

    /**
     * Create date-based header from strtotime()-compatible string
     *
     * @param int|string $time
     *
     * @return self
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function fromTimeString($time)
    {
        return static::fromTimestamp(strtotime($time));
    }

    /**
     * Create date-based header from Unix timestamp
     *
     * @param int $time
     *
     * @return self
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function fromTimestamp($time)
    {
        $dateHeader = new static();

        if (! $time || ! is_numeric($time)) {
            throw new Exception\InvalidArgumentException(
                'Invalid time for "' . $dateHeader->getFieldName() . '" header string'
            );
        }

        $dateHeader->setDate(new DateTime('@' . $time));

        return $dateHeader;
    }

    /**
     * Set date output format
     *
     * @param int $format
     * @throws Exception\InvalidArgumentException
     */
    public static function setDateFormat($format)
    {
        if (!isset(static::$dateFormats[$format])) {
            throw new Exception\InvalidArgumentException(
                "No constant defined for provided date format: {$format}"
            );
        }

        static::$dateFormat = static::$dateFormats[$format];
    }

    /**
     * Return current date output format
     *
     * @return string
     */
    public static function getDateFormat()
    {
        return static::$dateFormat;
    }

    /**
     * Set the date for this header, this can be a string or an instance of \DateTime
     *
     * @param string|DateTime $date
     * @return AbstractDate
     * @throws Exception\InvalidArgumentException
     */
    public function setDate($date)
    {
        if (is_string($date)) {
            try {
                $date = new DateTime($date, new DateTimeZone('GMT'));
            } catch (\Exception $e) {
                throw new Exception\InvalidArgumentException(
                    sprintf('Invalid date passed as string (%s)', (string) $date),
                    $e->getCode(),
                    $e
                );
            }
        } elseif (!($date instanceof DateTime)) {
            throw new Exception\InvalidArgumentException('Date must be an instance of \DateTime or a string');
        }

        $date->setTimezone(new DateTimeZone('GMT'));
        $this->date = $date;

        return $this;
    }

    /**
     * Return date for this header
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date()->format(static::$dateFormat);
    }

    /**
     * Return date for this header as an instance of \DateTime
     *
     * @return DateTime
     */
    public function date()
    {
        if ($this->date === null) {
            $this->date = new DateTime(null, new DateTimeZone('GMT'));
        }
        return $this->date;
    }

    /**
     * Compare provided date to date for this header
     * Returns < 0 if date in header is less than $date; > 0 if it's greater, and 0 if they are equal.
     * @see \strcmp()
     *
     * @param string|DateTime $date
     * @return int
     * @throws Exception\InvalidArgumentException
     */
    public function compareTo($date)
    {
        if (is_string($date)) {
            try {
                $date = new DateTime($date, new DateTimeZone('GMT'));
            } catch (\Exception $e) {
                throw new Exception\InvalidArgumentException(
                    sprintf('Invalid Date passed as string (%s)', (string) $date),
                    $e->getCode(),
                    $e
                );
            }
        } elseif (!($date instanceof DateTime)) {
            throw new Exception\InvalidArgumentException('Date must be an instance of \DateTime or a string');
        }

        $dateTimestamp = $date->getTimestamp();
        $thisTimestamp = $this->date()->getTimestamp();

        return ($thisTimestamp === $dateTimestamp) ? 0 : (($thisTimestamp > $dateTimestamp) ? 1 : -1);
    }

    /**
     * Get header value as formatted date
     *
     * @return string
     */
    public function getFieldValue()
    {
        return $this->getDate();
    }

    /**
     * Return header line
     *
     * @return string
     */
    public function toString()
    {
        return $this->getFieldName() . ': ' . $this->getDate();
    }

    /**
     * Allow casting to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
