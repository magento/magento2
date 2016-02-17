<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

use ArrayObject;

/**
 * @see http://www.ietf.org/rfc/rfc2109.txt
 * @see http://www.w3.org/Protocols/rfc2109/rfc2109
 */
class Cookie extends ArrayObject implements HeaderInterface
{
    protected $encodeValue = true;

    public static function fromSetCookieArray(array $setCookies)
    {
        $nvPairs = array();

        foreach ($setCookies as $setCookie) {
            if (! $setCookie instanceof SetCookie) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '%s requires an array of SetCookie objects',
                    __METHOD__
                ));
            }

            if (array_key_exists($setCookie->getName(), $nvPairs)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Two cookies with the same name were provided to %s',
                    __METHOD__
                ));
            }

            $nvPairs[$setCookie->getName()] = $setCookie->getValue();
        }

        return new static($nvPairs);
    }

    public static function fromString($headerLine)
    {
        $header = new static();

        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'cookie') {
            throw new Exception\InvalidArgumentException('Invalid header line for Server string: "' . $name . '"');
        }

        $nvPairs = preg_split('#;\s*#', $value);

        $arrayInfo = array();
        foreach ($nvPairs as $nvPair) {
            $parts = explode('=', $nvPair, 2);
            if (count($parts) != 2) {
                throw new Exception\RuntimeException('Malformed Cookie header found');
            }
            list($name, $value) = $parts;
            $arrayInfo[$name] = urldecode($value);
        }

        $header->exchangeArray($arrayInfo);

        return $header;
    }

    public function __construct(array $array = array())
    {
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    public function setEncodeValue($encodeValue)
    {
        $this->encodeValue = (bool) $encodeValue;
        return $this;
    }

    public function getEncodeValue()
    {
        return $this->encodeValue;
    }

    public function getFieldName()
    {
        return 'Cookie';
    }

    public function getFieldValue()
    {
        $nvPairs = array();

        foreach ($this as $name => $value) {
            $nvPairs[] = $name . '=' . (($this->encodeValue) ? urlencode($value) : $value);
        }

        return implode('; ', $nvPairs);
    }

    public function toString()
    {
        return 'Cookie: ' . $this->getFieldValue();
    }

    /**
     * Get the cookie as a string, suitable for sending as a "Cookie" header in an
     * HTTP request
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
