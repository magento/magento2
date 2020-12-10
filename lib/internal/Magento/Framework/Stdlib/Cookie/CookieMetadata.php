<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Cookie;

/**
 * Cookie Attributes
 * @api
 * @since 100.0.2
 */
class CookieMetadata
{
    /**#@+
     * Constant for metadata value key.
     */
    public const KEY_DOMAIN = 'domain';
    public const KEY_PATH = 'path';
    public const KEY_SECURE = 'secure';
    public const KEY_HTTP_ONLY = 'http_only';
    public const KEY_DURATION = 'duration';
    public const KEY_SAME_SITE = 'samesite';
    private const SAME_SITE_ALLOWED_VALUES = [
        'strict' => 'Strict',
        'lax' => 'Lax',
        'none' => 'None',
    ];
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
        if (isset($metadata[self::KEY_SAME_SITE])) {
            $this->setSameSite($metadata[self::KEY_SAME_SITE]);
        }
    }

    /**
     * Returns an array representation of this metadata.
     *
     * If a value has not yet been set then the key will not show up in the array.
     *
     * @return array
     */
    public function __toArray() //phpcs:ignore PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames
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

    /**
     * Setter for Cookie SameSite attribute
     *
     * @param  string $sameSite
     * @return $this
     */
    public function setSameSite(string $sameSite): CookieMetadata
    {
        if (!array_key_exists(strtolower($sameSite), self::SAME_SITE_ALLOWED_VALUES)) {
            throw new \InvalidArgumentException(
                'Invalid argument provided for SameSite directive expected one of: Strict, Lax or None'
            );
        }
        if (!$this->getSecure() && strtolower($sameSite) === 'none') {
            throw new \InvalidArgumentException(
                'Cookie must be secure in order to use the SameSite None directive.'
            );
        }
        $sameSite = self::SAME_SITE_ALLOWED_VALUES[strtolower($sameSite)];
        return $this->set(self::KEY_SAME_SITE, $sameSite);
    }

    /**
     * Get Same Site Flag
     *
     * @return string
     */
    public function getSameSite(): string
    {
        return $this->get(self::KEY_SAME_SITE);
    }
}
