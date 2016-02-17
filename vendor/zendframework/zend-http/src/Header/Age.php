<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

/**
 * Age HTTP Header
 *
 * @link       http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.6
 */
class Age implements HeaderInterface
{
    /**
     * Estimate of the amount of time in seconds since the response
     *
     * @var int
     */
    protected $deltaSeconds;

    /**
     * Create Age header from string
     *
     * @param string $headerLine
     * @return Age
     * @throws Exception\InvalidArgumentException
     */
    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'age') {
            throw new Exception\InvalidArgumentException('Invalid header line for Age string: "' . $name . '"');
        }

        $header = new static($value);

        return $header;
    }

    public function __construct($deltaSeconds = null)
    {
        if ($deltaSeconds) {
            $this->setDeltaSeconds($deltaSeconds);
        }
    }

    /**
     * Get header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Age';
    }

    /**
     * Get header value (number of seconds)
     *
     * @return int
     */
    public function getFieldValue()
    {
        return $this->getDeltaSeconds();
    }

    /**
     * Set number of seconds
     *
     * @param int $delta
     * @return RetryAfter
     */
    public function setDeltaSeconds($delta)
    {
        if (! is_int($delta) && ! is_numeric($delta)) {
            throw new Exception\InvalidArgumentException('Invalid delta provided');
        }
        $this->deltaSeconds = (int) $delta;
        return $this;
    }

    /**
     * Get number of seconds
     *
     * @return int
     */
    public function getDeltaSeconds()
    {
        return $this->deltaSeconds;
    }

    /**
     * Return header line
     * In case of overflow RFC states to set value of 2147483648 (2^31)
     *
     * @return string
     */
    public function toString()
    {
        return 'Age: ' . (($this->deltaSeconds >= PHP_INT_MAX) ? '2147483648' : $this->deltaSeconds);
    }
}
