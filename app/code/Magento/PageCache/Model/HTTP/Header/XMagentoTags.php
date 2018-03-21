<?php

namespace Magento\PageCache\Model\HTTP\Header;

use Zend\Http\Header\MultipleHeaderInterface;
use Zend\Http\Header\GenericHeader;
use Zend\Http\Header\HeaderValue;
use Zend\Http\Header\Exception;

class XMagentoTags implements MultipleHeaderInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * XMagentoTags constructor.
     *
     * @param string|null $value
     */
    public function __construct($value = null)
    {
        if ($value) {
            HeaderValue::assertValid($value);
            $this->value = $value;
        }
    }

    /**
     * Create X-Magento-Tags header from a given header line
     *
     * @param string $headerLine The header line to parse.
     *
     * @return self
     * @throws Exception\InvalidArgumentException If the name field in the given header line does not match.
     */
    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'x-magento-tags') {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid header line for X-Magento-Tags string: "%s"',
                    $name
                )
            );
        }

        // @todo implementation details
        $header = new static($value);

        return $header;
    }

    /**
     * Cast multiple header objects to a single string header
     *
     * @param  array $headers
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function toStringMultipleHeaders(array $headers)
    {
        $name = $this->getFieldName();
        $values = array($this->getFieldValue());
        foreach ($headers as $header) {
            if (!$header instanceof static) {
                throw new Exception\InvalidArgumentException(
                    'This method toStringMultipleHeaders was expecting an array of headers of the same type'
                );
            }
            $values[] = $header->getFieldValue();
        }

        return $name . ': ' . implode(',', $values) . "\r\n";
    }

    /**
     * Get the header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'X-Magento-Tags';
    }

    /**
     * Get the header value
     *
     * @return string
     */
    public function getFieldValue()
    {
        return $this->value;
    }

    /**
     * Return the header as a string
     *
     * @return string
     */
    public function toString()
    {
        return 'X-Magento-Tags: ' . $this->getFieldValue();
    }
}
