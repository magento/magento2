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
 * Content-Location Header
 *
  */
class GenericHeader implements HeaderInterface
{
    /**
     * @var string
     */
    protected $fieldName = null;

    /**
     * @var string
     */
    protected $fieldValue = null;

    /**
     * Factory to generate a header object from a string
     *
     * @static
     * @param string $headerLine
     * @return GenericHeader
     */
    public static function fromString($headerLine)
    {
        list($fieldName, $fieldValue) = GenericHeader::splitHeaderLine($headerLine);
        $header = new static($fieldName, $fieldValue);
        return $header;
    }

    /**
     * Splits the header line in `name` and `value` parts.
     *
     * @param string $headerLine
     * @return string[] `name` in the first index and `value` in the second.
     * @throws Exception\InvalidArgumentException If header does not match with the format ``name:value``
     */
    public static function splitHeaderLine($headerLine)
    {
        $parts = explode(':', $headerLine, 2);
        if (count($parts) !== 2) {
            throw new Exception\InvalidArgumentException('Header must match with the format "name:value"');
        }

        if (! HeaderValue::isValid($parts[1])) {
            throw new Exception\InvalidArgumentException('Invalid header value detected');
        }

        $parts[1] = ltrim($parts[1]);

        return $parts;
    }

    /**
     * Constructor
     *
     * @param null|string $fieldName
     * @param null|string $fieldValue
     */
    public function __construct($fieldName = null, $fieldValue = null)
    {
        if ($fieldName) {
            $this->setFieldName($fieldName);
        }

        if ($fieldValue !== null) {
            $this->setFieldValue($fieldValue);
        }
    }

    /**
     * Set header field name
     *
     * @param  string $fieldName
     * @return GenericHeader
     * @throws Exception\InvalidArgumentException If the name does not match with RFC 2616 format.
     */
    public function setFieldName($fieldName)
    {
        if (!is_string($fieldName) || empty($fieldName)) {
            throw new Exception\InvalidArgumentException('Header name must be a string');
        }

        /*
         * Following RFC 7230 section 3.2
         *
         * header-field = field-name ":" [ field-value ]
         * field-name   = token
         * token        = 1*tchar
         * tchar        = "!" / "#" / "$" / "%" / "&" / "'" / "*" / "+" / "-" / "." /
         *                "^" / "_" / "`" / "|" / "~" / DIGIT / ALPHA
         */
        if (!preg_match('/^[!#$%&\'*+\-\.\^_`|~0-9a-zA-Z]+$/', $fieldName)) {
            throw new Exception\InvalidArgumentException(
                'Header name must be a valid RFC 7230 (section 3.2) field-name.'
            );
        }

        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * Retrieve header field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set header field value
     *
     * @param  string $fieldValue
     * @return GenericHeader
     */
    public function setFieldValue($fieldValue)
    {
        $fieldValue = (string) $fieldValue;
        HeaderValue::assertValid($fieldValue);

        if (preg_match('/^\s+$/', $fieldValue)) {
            $fieldValue = '';
        }

        $this->fieldValue = $fieldValue;
        return $this;
    }

    /**
     * Retrieve header field value
     *
     * @return string
     */
    public function getFieldValue()
    {
        return $this->fieldValue;
    }

    /**
     * Cast to string as a well formed HTTP header line
     *
     * Returns in form of "NAME: VALUE\r\n"
     *
     * @return string
     */
    public function toString()
    {
        return $this->getFieldName() . ': ' . $this->getFieldValue();
    }
}
