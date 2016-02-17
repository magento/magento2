<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Uri;

/**
 * HTTP URI handler
 */
class Http extends Uri
{
    /**
     * @see Uri::$validSchemes
     */
    protected static $validSchemes = array(
        'http',
        'https'
    );

    /**
     * @see Uri::$defaultPorts
     */
    protected static $defaultPorts = array(
        'http'  => 80,
        'https' => 443,
    );

    /**
     * @see Uri::$validHostTypes
     */
    protected $validHostTypes = self::HOST_DNS_OR_IPV4_OR_IPV6_OR_REGNAME;

    /**
     * User name as provided in authority of URI
     * @var null|string
     */
    protected $user;

    /**
     * Password as provided in authority of URI
     * @var null|string
     */
    protected $password;

    /**
     * Get the username part (before the ':') of the userInfo URI part
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the password part (after the ':') of the userInfo URI part
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the User-info (usually user:password) part
     *
     * @return string|null
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * Set the username part (before the ':') of the userInfo URI part
     *
     * @param string|null $user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = null === $user ? null : (string) $user;

        $this->buildUserInfo();

        return $this;
    }

    /**
     * Set the password part (after the ':') of the userInfo URI part
     *
     * @param  string $password
     *
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = null === $password ? null : (string) $password;

        $this->buildUserInfo();

        return $this;
    }

    /**
     * Set the URI User-info part (usually user:password)
     *
     * @param  string|null $userInfo
     *
     * @return self
     *
     * @throws Exception\InvalidUriPartException If the schema definition does not have this part
     */
    public function setUserInfo($userInfo)
    {
        $this->userInfo = null === $userInfo ? null : (string) $userInfo;

        $this->parseUserInfo();

        return $this;
    }

    /**
     * Validate the host part of an HTTP URI
     *
     * This overrides the common URI validation method with a DNS or IP only
     * default. Users may still enforce allowing other host types.
     *
     * @param  string  $host
     * @param  int $allowed
     * @return bool
     */
    public static function validateHost($host, $allowed = self::HOST_DNS_OR_IPV4_OR_IPV6)
    {
        return parent::validateHost($host, $allowed);
    }

    /**
     * Parse the user info into username and password segments
     *
     * Parses the user information into username and password segments, and
     * then sets the appropriate values.
     *
     * @return void
     */
    protected function parseUserInfo()
    {
        // No user information? we're done
        if (null === $this->userInfo) {
            $this->setUser(null);
            $this->setPassword(null);

            return;
        }

        // If no ':' separator, we only have a username
        if (false === strpos($this->userInfo, ':')) {
            $this->setUser($this->userInfo);
            $this->setPassword(null);
            return;
        }

        // Split on the ':', and set both user and password
        list($this->user, $this->password) = explode(':', $this->userInfo, 2);
    }

    /**
     * Build the user info based on user and password
     *
     * Builds the user info based on the given user and password values
     *
     * @return void
     */
    protected function buildUserInfo()
    {
        if (null !== $this->password) {
            $this->userInfo = $this->user . ':' . $this->password;
        } else {
            $this->userInfo = $this->user;
        }
    }

    /**
     * Return the URI port
     *
     * If no port is set, will return the default port according to the scheme
     *
     * @return int
     * @see    Zend\Uri\Uri::getPort()
     */
    public function getPort()
    {
        if (empty($this->port)) {
            if (array_key_exists($this->scheme, static::$defaultPorts)) {
                return static::$defaultPorts[$this->scheme];
            }
        }
        return $this->port;
    }

    /**
     * Parse a URI string
     *
     * @param  string $uri
     * @return Http
     */
    public function parse($uri)
    {
        parent::parse($uri);

        if (empty($this->path)) {
            $this->path = '/';
        }

        return $this;
    }
}
