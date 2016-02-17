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
 * @throws Exception\InvalidArgumentException
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
 */
class CacheControl implements HeaderInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Array of Cache-Control directives
     *
     * @var array
     */
    protected $directives = array();

    /**
     * Creates a CacheControl object from a headerLine
     *
     * @param string $headerLine
     * @throws Exception\InvalidArgumentException
     * @return CacheControl
     */
    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'cache-control') {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid header line for Cache-Control string: ""',
                $name
            ));
        }

        HeaderValue::assertValid($value);
        $directives = static::parseValue($value);

        // @todo implementation details
        $header = new static();
        foreach ($directives as $key => $value) {
            $header->addDirective($key, $value);
        }

        return $header;
    }

    /**
     * Required from HeaderDescription interface
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Cache-Control';
    }

    /**
     * Checks if the internal directives array is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->directives);
    }

    /**
     * Add a directive
     * For directives like 'max-age=60', $value = '60'
     * For directives like 'private', use the default $value = true
     *
     * @param string $key
     * @param string|bool $value
     * @return CacheControl - provides the fluent interface
     */
    public function addDirective($key, $value = true)
    {
        HeaderValue::assertValid($key);
        if (! is_bool($value)) {
            HeaderValue::assertValid($value);
        }
        $this->directives[$key] = $value;
        return $this;
    }

    /**
     * Check the internal directives array for a directive
     *
     * @param string $key
     * @return bool
     */
    public function hasDirective($key)
    {
        return array_key_exists($key, $this->directives);
    }

    /**
     * Fetch the value of a directive from the internal directive array
     *
     * @param string $key
     * @return string|null
     */
    public function getDirective($key)
    {
        return array_key_exists($key, $this->directives) ? $this->directives[$key] : null;
    }

    /**
     * Remove a directive
     *
     * @param string $key
     * @return CacheControl - provides the fluent interface
     */
    public function removeDirective($key)
    {
        unset($this->directives[$key]);
        return $this;
    }

    /**
     * Assembles the directives into a comma-delimited string
     *
     * @return string
     */
    public function getFieldValue()
    {
        $parts = array();
        ksort($this->directives);
        foreach ($this->directives as $key => $value) {
            if (true === $value) {
                $parts[] = $key;
            } else {
                if (preg_match('#[^a-zA-Z0-9._-]#', $value)) {
                    $value = '"' . $value.'"';
                }
                $parts[] = "$key=$value";
            }
        }
        return implode(', ', $parts);
    }

    /**
     * Returns a string representation of the HTTP Cache-Control header
     *
     * @return string
     */
    public function toString()
    {
        return 'Cache-Control: ' . $this->getFieldValue();
    }

    /**
     * Internal function for parsing the value part of a
     * HTTP Cache-Control header
     *
     * @param string $value
     * @throws Exception\InvalidArgumentException
     * @return array
     */
    protected static function parseValue($value)
    {
        $value = trim($value);

        $directives = array();

        // handle empty string early so we don't need a separate start state
        if ($value == '') {
            return $directives;
        }

        $lastMatch = null;

        state_directive:
        switch (static::match(array('[a-zA-Z][a-zA-Z_-]*'), $value, $lastMatch)) {
            case 0:
                $directive = $lastMatch;
                goto state_value;
                // intentional fall-through

            default:
                throw new Exception\InvalidArgumentException('expected DIRECTIVE');
        }

        state_value:
        switch (static::match(array('="[^"]*"', '=[^",\s;]*'), $value, $lastMatch)) {
            case 0:
                $directives[$directive] = substr($lastMatch, 2, -1);
                goto state_separator;
                // intentional fall-through

            case 1:
                $directives[$directive] = rtrim(substr($lastMatch, 1));
                goto state_separator;
                // intentional fall-through

            default:
                $directives[$directive] = true;
                goto state_separator;
        }

        state_separator:
        switch (static::match(array('\s*,\s*', '$'), $value, $lastMatch)) {
            case 0:
                goto state_directive;
                // intentional fall-through

            case 1:
                return $directives;

            default:
                throw new Exception\InvalidArgumentException('expected SEPARATOR or END');

        }
    }

    /**
     * Internal function used by parseValue to match tokens
     *
     * @param array $tokens
     * @param string $string
     * @param string $lastMatch
     * @return int
     */
    protected static function match($tokens, &$string, &$lastMatch)
    {
        // Ensure we have a string
        $value = (string) $string;

        foreach ($tokens as $i => $token) {
            if (preg_match('/^' . $token . '/', $value, $matches)) {
                $lastMatch = $matches[0];
                $string = substr($value, strlen($matches[0]));
                return $i;
            }
        }
        return -1;
    }
}
