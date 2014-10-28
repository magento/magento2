<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Stdlib\Cookie;

/**
 * Class PublicCookieMetadata
 *
 */
class PublicCookieMetadata extends CookieMetadata
{
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
        return $this->set(self::KEY_SECURE, $secure);
    }
}
