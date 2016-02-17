<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Header
 * @version    $Id$
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Http_Header_Exception_InvalidArgumentException
 */
#require_once "Zend/Http/Header/Exception/InvalidArgumentException.php";

/**
 * @see Zend_Http_Header_Exception_RuntimeException
 */
#require_once "Zend/Http/Header/Exception/RuntimeException.php";

/**
 * @see Zend_Http_Header_HeaderValue
 */
#require_once "Zend/Http/Header/HeaderValue.php";

/**
 * Zend_Http_Client is an implementation of an HTTP client in PHP. The client
 * supports basic features like sending different HTTP requests and handling
 * redirections, as well as more advanced features like proxy settings, HTTP
 * authentication and cookie persistence (using a Zend_Http_CookieJar object)
 *
 * @todo Implement proxy settings
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Header
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_Header_SetCookie
{

    /**
     * Cookie name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Cookie value
     *
     * @var string
     */
    protected $value = null;

    /**
     * Version
     *
     * @var integer
     */
    protected $version = null;

    /**
     * Max Age
     *
     * @var integer
     */
    protected $maxAge = null;

    /**
     * Cookie expiry date
     *
     * @var int
     */
    protected $expires = null;

    /**
     * Cookie domain
     *
     * @var string
     */
    protected $domain = null;

    /**
     * Cookie path
     *
     * @var string
     */
    protected $path = null;

    /**
     * Whether the cookie is secure or not
     *
     * @var boolean
     */
    protected $secure = null;

    /**
     * @var true
     */
    protected $httponly = null;

    /**
     * Generate a new Cookie object from a cookie string
     * (for example the value of the Set-Cookie HTTP header)
     *
     * @static
     * @throws Zend_Http_Header_Exception_InvalidArgumentException
     * @param  $headerLine
     * @param  bool $bypassHeaderFieldName
     * @return array|SetCookie
     */
    public static function fromString($headerLine, $bypassHeaderFieldName = false)
    {
        list($name, $value) = explode(': ', $headerLine, 2);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'set-cookie') {
            throw new Zend_Http_Header_Exception_InvalidArgumentException('Invalid header line for Set-Cookie string: "' . $name . '"');
        }

        $multipleHeaders = preg_split('#(?<!Sun|Mon|Tue|Wed|Thu|Fri|Sat),\s*#', $value);
        $headers = array();
        foreach ($multipleHeaders as $headerLine) {
            $header = new self();
            $keyValuePairs = preg_split('#;\s*#', $headerLine);
            foreach ($keyValuePairs as $keyValue) {
                if (strpos($keyValue, '=')) {
                    list($headerKey, $headerValue) = preg_split('#=\s*#', $keyValue, 2);
                } else {
                    $headerKey = $keyValue;
                    $headerValue = null;
                }

                // First K=V pair is always the cookie name and value
                if ($header->getName() === NULL) {
                    $header->setName($headerKey);
                    $header->setValue($headerValue);
                    continue;
                }

                // Process the remanining elements
                switch (str_replace(array('-', '_'), '', strtolower($headerKey))) {
                    case 'expires' : $header->setExpires($headerValue); break;
                    case 'domain'  : $header->setDomain($headerValue); break;
                    case 'path'    : $header->setPath($headerValue); break;
                    case 'secure'  : $header->setSecure(true); break;
                    case 'httponly': $header->setHttponly(true); break;
                    case 'version' : $header->setVersion((int) $headerValue); break;
                    case 'maxage'  : $header->setMaxAge((int) $headerValue); break;
                    default:
                        // Intentionally omitted
                }
            }
            $headers[] = $header;
        }
        return count($headers) == 1 ? array_pop($headers) : $headers;
    }

    /**
     * Cookie object constructor
     *
     * @todo Add validation of each one of the parameters (legal domain, etc.)
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param string $maxAge
     * @param int $version
     * @return SetCookie
     */
    public function __construct($name = null, $value = null, $expires = null, $path = null, $domain = null, $secure = false, $httponly = false, $maxAge = null, $version = null)
    {
        $this->type = 'Cookie';

        if ($name) {
            $this->setName($name);
        }

        if ($value) {
            $this->setValue($value); // in parent
        }

        if ($version) {
            $this->setVersion($version);
        }

        if ($maxAge) {
            $this->setMaxAge($maxAge);
        }

        if ($domain) {
            $this->setDomain($domain);
        }

        if ($expires) {
            $this->setExpires($expires);
        }

        if ($path) {
            $this->setPath($path);
        }

        if ($secure) {
            $this->setSecure($secure);
        }

        if ($httponly) {
            $this->setHttponly($httponly);
        }
    }

    /**
     * @return string 'Set-Cookie'
     */
    public function getFieldName()
    {
        return 'Set-Cookie';
    }

    /**
     * @throws Zend_Http_Header_Exception_RuntimeException
     * @return string
     */
    public function getFieldValue()
    {
        if ($this->getName() == '') {
            throw new Zend_Http_Header_Exception_RuntimeException('A cookie name is required to generate a field value for this cookie');
        }

        $value = $this->getValue();
        if (strpos($value,'"')!==false) {
            $value = '"'.urlencode(str_replace('"', '', $value)).'"';
        } else {
            $value = urlencode($value);
        }
        $fieldValue = $this->getName() . '=' . $value;

        $version = $this->getVersion();
        if ($version!==null) {
            $fieldValue .= '; Version=' . $version;
        }

        $maxAge = $this->getMaxAge();
        if ($maxAge!==null) {
            $fieldValue .= '; Max-Age=' . $maxAge;
        }

        $expires = $this->getExpires();
        if ($expires) {
            $fieldValue .= '; Expires=' . $expires;
        }

        $domain = $this->getDomain();
        if ($domain) {
            $fieldValue .= '; Domain=' . $domain;
        }

        $path = $this->getPath();
        if ($path) {
            $fieldValue .= '; Path=' . $path;
        }

        if ($this->isSecure()) {
            $fieldValue .= '; Secure';
        }

        if ($this->isHttponly()) {
            $fieldValue .= '; HttpOnly';
        }

        return $fieldValue;
    }

    /**
     * @param string $name
     * @return SetCookie
     */
    public function setName($name)
    {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new Zend_Http_Header_Exception_InvalidArgumentException("Cookie name cannot contain these characters: =,; \\t\\r\\n\\013\\014 ({$name})");
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        Zend_Http_Header_HeaderValue::assertValid($value);
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set version
     *
     * @param integer $version
     */
    public function setVersion($version)
    {
        if (!is_int($version)) {
            throw new Zend_Http_Header_Exception_InvalidArgumentException('Invalid Version number specified');
        }
        $this->version = $version;
    }

    /**
     * Get version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set Max-Age
     *
     * @param integer $maxAge
     */
    public function setMaxAge($maxAge)
    {
        if (!is_int($maxAge) || ($maxAge<0)) {
            throw new Zend_Http_Header_Exception_InvalidArgumentException('Invalid Max-Age number specified');
        }
        $this->maxAge = $maxAge;
    }

    /**
     * Get Max-Age
     *
     * @return integer
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * @param int $expires
     * @return SetCookie
     */
    public function setExpires($expires)
    {
        if (!empty($expires)) {
            if (is_string($expires)) {
                $expires = strtotime($expires);
            } elseif (!is_int($expires)) {
                throw new Zend_Http_Header_Exception_InvalidArgumentException('Invalid expires time specified');
            }
            $this->expires = (int) $expires;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getExpires($inSeconds = false)
    {
        if ($this->expires == null) {
            return;
        }
        if ($inSeconds) {
            return $this->expires;
        }
        return gmdate('D, d-M-Y H:i:s', $this->expires) . ' GMT';
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        Zend_Http_Header_HeaderValue::assertValid($domain);
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        Zend_Http_Header_HeaderValue::assertValid($path);
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param boolean $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @param bool $httponly
     */
    public function setHttponly($httponly)
    {
        $this->httponly = $httponly;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHttponly()
    {
        return $this->httponly;
    }

    /**
     * Check whether the cookie has expired
     *
     * Always returns false if the cookie is a session cookie (has no expiry time)
     *
     * @param int $now Timestamp to consider as "now"
     * @return boolean
     */
    public function isExpired($now = null)
    {
        if ($now === null) {
            $now = time();
        }

        if (is_int($this->expires) && $this->expires < $now) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether the cookie is a session cookie (has no expiry time set)
     *
     * @return boolean
     */
    public function isSessionCookie()
    {
        return ($this->expires === null);
    }

    public function isValidForRequest($requestDomain, $path, $isSecure = false)
    {
        if ($this->getDomain() && (strrpos($requestDomain, $this->getDomain()) !== false)) {
            return false;
        }

        if ($this->getPath() && (strpos($path, $this->getPath()) !== 0)) {
            return false;
        }

        if ($this->secure && $this->isSecure()!==$isSecure) {
            return false;
        }

        return true;

    }

    public function toString()
    {
        return $this->getFieldName() . ': ' . $this->getFieldValue();
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toStringMultipleHeaders(array $headers)
    {
        $headerLine = $this->toString();
        /* @var $header SetCookie */
        foreach ($headers as $header) {
            if (!$header instanceof Zend_Http_Header_SetCookie) {
                throw new Zend_Http_Header_Exception_RuntimeException(
                    'The SetCookie multiple header implementation can only accept an array of SetCookie headers'
                );
            }
            $headerLine .= ', ' . $header->getFieldValue();
        }
        return $headerLine;
    }


}
