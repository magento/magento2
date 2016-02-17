<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

use stdClass;

/**
 * @throws Exception\InvalidArgumentException
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.17
 */
class ContentType implements HeaderInterface
{
    /**
     * @var string
     */
    protected $mediaType;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $value;

    /**
     * Factory method: create an object from a string representation
     *
     * @param  string $headerLine
     * @return self
     */
    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'content-type') {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid header line for Content-Type string: "%s"',
                $name
            ));
        }

        $parts             = explode(';', $value);
        $mediaType         = array_shift($parts);
        $header = new static($value, trim($mediaType));

        if (count($parts) > 0) {
            $parameters = array();
            foreach ($parts as $parameter) {
                $parameter = trim($parameter);
                if (!preg_match('/^(?P<key>[^\s\=]+)\="?(?P<value>[^\s\"]*)"?$/', $parameter, $matches)) {
                    continue;
                }
                $parameters[$matches['key']] = $matches['value'];
            }
            $header->setParameters($parameters);
        }

        return $header;
    }

    public function __construct($value = null, $mediaType = null)
    {
        if ($value) {
            HeaderValue::assertValid($value);
            $this->value = $value;
        }
        $this->mediaType = $mediaType;
    }

    /**
     * Determine if the mediatype value in this header matches the provided criteria
     *
     * @param  array|string $matchAgainst
     * @return string|bool Matched value or false
     */
    public function match($matchAgainst)
    {
        if (is_string($matchAgainst)) {
            $matchAgainst = $this->splitMediaTypesFromString($matchAgainst);
        }

        $mediaType = $this->getMediaType();
        $left      = $this->getMediaTypeObjectFromString($mediaType);

        foreach ($matchAgainst as $matchType) {
            $matchType = strtolower($matchType);

            if ($mediaType == $matchType) {
                return $matchType;
            }

            $right = $this->getMediaTypeObjectFromString($matchType);

            // Is the right side a wildcard type?
            if ($right->type == '*') {
                if ($this->validateSubtype($right, $left)) {
                    return $matchType;
                }
            }

            // Do the types match?
            if ($right->type == $left->type) {
                if ($this->validateSubtype($right, $left)) {
                    return $matchType;
                }
            }
        }

        return false;
    }

    /**
     * Create a string representation of the header
     *
     * @return string
     */
    public function toString()
    {
        return 'Content-Type: ' . $this->getFieldValue();
    }

    /**
     * Get the field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Content-Type';
    }

    /**
     * Get the field value
     *
     * @return string
     */
    public function getFieldValue()
    {
        if (null !== $this->value) {
            return $this->value;
        }
        return $this->assembleValue();
    }

    /**
     * Set the media type
     *
     * @param  string $mediaType
     * @return self
     */
    public function setMediaType($mediaType)
    {
        HeaderValue::assertValid($mediaType);
        $this->mediaType = strtolower($mediaType);
        $this->value     = null;
        return $this;
    }

    /**
     * Get the media type
     *
     * @return string
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

    /**
     * Set additional content-type parameters
     *
     * @param  array $parameters
     * @return self
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            HeaderValue::assertValid($key);
            HeaderValue::assertValid($value);
        }
        $this->parameters = array_merge($this->parameters, $parameters);
        $this->value      = null;
        return $this;
    }

    /**
     * Get any additional content-type parameters currently set
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set the content-type character set encoding
     *
     * @param  string $charset
     * @return self
     */
    public function setCharset($charset)
    {
        HeaderValue::assertValid($charset);
        $this->parameters['charset'] = $charset;
        $this->value = null;
        return $this;
    }

    /**
     * Get the content-type character set encoding, if any
     *
     * @return null|string
     */
    public function getCharset()
    {
        if (isset($this->parameters['charset'])) {
            return $this->parameters['charset'];
        }
        return;
    }

    /**
     * Assemble the value based on the media type and any available parameters
     *
     * @return string
     */
    protected function assembleValue()
    {
        $mediaType = $this->getMediaType();
        if (empty($this->parameters)) {
            return $mediaType;
        }

        $parameters = array();
        foreach ($this->parameters as $key => $value) {
            $parameters[] = sprintf('%s=%s', $key, $value);
        }

        return sprintf('%s; %s', $mediaType, implode('; ', $parameters));
    }

    /**
     * Split comma-separated media types into an array
     *
     * @param  string $criteria
     * @return array
     */
    protected function splitMediaTypesFromString($criteria)
    {
        $mediaTypes = explode(',', $criteria);
        array_walk(
            $mediaTypes,
            function (&$value) {
                $value = trim($value);
            }
        );

        return $mediaTypes;
    }

    /**
     * Split a mediatype string into an object with the following parts:
     *
     * - type
     * - subtype
     * - format
     *
     * @param  string $string
     * @return stdClass
     */
    protected function getMediaTypeObjectFromString($string)
    {
        if (!is_string($string)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Non-string mediatype "%s" provided',
                (is_object($string) ? get_class($string) : gettype($string))
            ));
        }

        $parts = explode('/', $string, 2);
        if (1 == count($parts)) {
            throw new Exception\DomainException(sprintf(
                'Invalid mediatype "%s" provided',
                $string
            ));
        }

        $type    = array_shift($parts);
        $subtype = array_shift($parts);
        $format  = $subtype;
        if (strstr($subtype, '+')) {
            $parts   = explode('+', $subtype, 2);
            $subtype = array_shift($parts);
            $format  = array_shift($parts);
        }

        $mediaType = (object) array(
            'type'    => $type,
            'subtype' => $subtype,
            'format'  => $format,
        );

        return $mediaType;
    }

    /**
     * Validate a subtype
     *
     * @param  stdClass $right
     * @param  stdClass $left
     * @return bool
     */
    protected function validateSubtype($right, $left)
    {
        // Is the right side a wildcard subtype?
        if ($right->subtype == '*') {
            return $this->validateFormat($right, $left);
        }

        // Do the right side and left side subtypes match?
        if ($right->subtype == $left->subtype) {
            return $this->validateFormat($right, $left);
        }

        // Is the right side a partial wildcard?
        if ('*' == substr($right->subtype, -1)) {
            // validate partial-wildcard subtype
            if (!$this->validatePartialWildcard($right->subtype, $left->subtype)) {
                return false;
            }
            // Finally, verify format is valid
            return $this->validateFormat($right, $left);
        }

        // Does the right side subtype match the left side format?
        if ($right->subtype == $left->format) {
            return true;
        }

        // At this point, there is no valid match
        return false;
    }

    /**
     * Validate the format
     *
     * Validate that the right side format matches what the left side defines.
     *
     * @param  string $right
     * @param  string $left
     * @return bool
     */
    protected function validateFormat($right, $left)
    {
        if ($right->format && $left->format) {
            if ($right->format == '*') {
                return true;
            }
            if ($right->format == $left->format) {
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Validate a partial wildcard (i.e., string ending in '*')
     *
     * @param  string $right
     * @param  string $left
     * @return bool
     */
    protected function validatePartialWildcard($right, $left)
    {
        $requiredSegment = substr($right, 0, strlen($right) - 1);
        if ($requiredSegment == $left) {
            return true;
        }

        if (strlen($requiredSegment) >= strlen($left)) {
            return false;
        }

        if (0 === strpos($left, $requiredSegment)) {
            return true;
        }

        return false;
    }
}
