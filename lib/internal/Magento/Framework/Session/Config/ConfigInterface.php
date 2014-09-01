<?php
/**
 * Session config interface
 *
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
namespace Magento\Framework\Session\Config;

interface ConfigInterface
{
    /**
     * Set array of options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options);

    /**
     * Get all options set
     *
     * @return array
     */
    public function getOptions();

    /**
     * Set an individual option
     *
     * @param string $option
     * @param mixed $value
     * @return $this
     */
    public function setOption($option, $value);

    /**
     * Get an individual option
     *
     * @param string $option
     * @return mixed
     */
    public function getOption($option);

    /**
     * Convert config to array
     *
     * @return array
     */
    public function toArray();

    /**
     * Set session.name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get session.name
     *
     * @return string
     */
    public function getName();

    /**
     * Set session.save_path
     *
     * @param string $savePath
     * @return $this
     */
    public function setSavePath($savePath);

    /**
     * Set session.save_path
     *
     * @return string
     */
    public function getSavePath();

    /**
     * Set session.cookie_lifetime
     *
     * @param int $cookieLifetime
     * @return $this
     */
    public function setCookieLifetime($cookieLifetime);

    /**
     * Get session.cookie_lifetime
     *
     * @return int
     */
    public function getCookieLifetime();

    /**
     * Set session.cookie_path
     *
     * @param string $cookiePath
     * @return $this
     */
    public function setCookiePath($cookiePath);

    /**
     * Get session.cookie_path
     *
     * @return string
     */
    public function getCookiePath();

    /**
     * Set session.cookie_domain
     *
     * @param string $cookieDomain
     * @return $this
     */
    public function setCookieDomain($cookieDomain);

    /**
     * Get session.cookie_domain
     *
     * @return string
     */
    public function getCookieDomain();

    /**
     * Set session.cookie_secure
     *
     * @param bool $cookieSecure
     * @return $this
     */
    public function setCookieSecure($cookieSecure);

    /**
     * Get session.cookie_secure
     *
     * @return bool
     */
    public function getCookieSecure();

    /**
     * Set session.cookie_httponly
     *
     * @param bool $cookieHttpOnly
     * @return $this
     */
    public function setCookieHttpOnly($cookieHttpOnly);

    /**
     * Get session.cookie_httponly
     *
     * @return bool
     */
    public function getCookieHttpOnly();

    /**
     * Set session.use_cookies
     *
     * @param bool $useCookies
     * @return $this
     */
    public function setUseCookies($useCookies);

    /**
     * Get session.use_cookies
     *
     * @return bool
     */
    public function getUseCookies();
}
