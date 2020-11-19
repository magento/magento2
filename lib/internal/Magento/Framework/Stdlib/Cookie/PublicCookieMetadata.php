<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Cookie;

/**
 * Public Cookie Attributes
 *
 * @api
 * @since 100.0.2
 */
class PublicCookieMetadata extends CookieMetadata
{
    /**
     * @param array $metadata
     */
    public function __construct($metadata = [])
    {
        if (!isset($metadata[self::KEY_SAME_SITE])) {
            $metadata[self::KEY_SAME_SITE] = 'Lax';
        }
        parent::__construct($metadata);
    }

    /**
     * Set the number of seconds until the cookie expires
     *
     * The cookie duration can be translated into an expiration date at the time the cookie is sent.
     *
     * @param int $duration Time in seconds.
     * @return $this
     */
    public function setDuration($duration)
    {
        return $this->set(self::KEY_DURATION, $duration);
    }

    /**
     * Set the cookie duration to one year
     *
     * @return $this
     */
    public function setDurationOneYear()
    {
        return $this->setDuration(3600 * 24 * 365);
    }

    /**
     * Get the number of seconds until the cookie expires
     *
     * The cookie duration can be translated into an expiration date at the time the cookie is sent.
     *
     * @return int|null Time in seconds.
     */
    public function getDuration()
    {
        return $this->get(self::KEY_DURATION);
    }

    /**
     * Set HTTPOnly flag
     *
     * @param bool $httpOnly
     * @return $this
     */
    public function setHttpOnly($httpOnly)
    {
        return $this->set(self::KEY_HTTP_ONLY, $httpOnly);
    }

    /**
     * Set whether the cookie is only available under HTTPS
     *
     * @param bool $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        if (!$secure && $this->get(self::KEY_SAME_SITE) === 'None') {
            throw new \InvalidArgumentException(
                'Cookie must be secure in order to use the SameSite None directive.'
            );
        }
        return $this->set(self::KEY_SECURE, $secure);
    }
}
