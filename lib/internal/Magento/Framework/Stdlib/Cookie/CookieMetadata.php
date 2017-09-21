<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Cookie;

/**
 * Class CookieMetadata
 * @api
 */
class CookieMetadata
{
    /**#@+
     * Constant for metadata value key.
     */
    const KEY_DOMAIN = 'domain';
    const KEY_PATH = 'path';
    const KEY_SECURE = 'secure';
    const KEY_HTTP_ONLY = 'http_only';
    const KEY_DURATION = 'duration';
    /**#@-*/

    /**#@-*/
    private $metadata;

    /**
     * @param array $metadata
     */
    public function __construct($metadata = [])
    {
        if (!is_array($metadata)) {
            $metadata = [];
        }
        $this->metadata = $metadata;
    }

    /**
     * Returns an array representation of this metadata.
     *
     * If a value has not yet been set then the key will not show up in the array.
     *
     * @return array
     */
    public function __toArray()
    {
        return $this->metadata;
    }

    /**
     * Set the domain for the cookie
     *
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        return $this->set(self::KEY_DOMAIN, $domain);
    }

    /**
     * Get the domain for the cookie
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->get(self::KEY_DOMAIN);
    }

    /**
     * Set path of the cookie
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        return $this->set(self::KEY_PATH, $path);
    }

    /**
     * Get the path of the cookie
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->get(self::KEY_PATH);
    }

    /**
     * Get a value from the metadata storage.
     *
     * @param string $name
     * @return int|float|string|bool|null
     */
    protected function get($name)
    {
        if (isset($this->metadata[$name])) {
            return $this->metadata[$name];
        }
        return null;
    }

    /**
     * Set a value to the metadata storage.
     *
     * @param string $name
     * @param int|float|string|bool|null $value
     * @return $this
     */
    protected function set($name, $value)
    {
        $this->metadata[$name] = $value;
        return $this;
    }

    /**
     * Get HTTP Only flag
     *
     * @return bool|null
     */
    public function getHttpOnly()
    {
        return $this->get(self::KEY_HTTP_ONLY);
    }

    /**
     * Get whether the cookie is only available under HTTPS
     *
     * @return bool|null
     */
    public function getSecure()
    {
        return $this->get(self::KEY_SECURE);
    }
}
