<?php
/**
 * Session config interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Config;

/**
 * Interface \Magento\Framework\Session\Config\ConfigInterface
 *
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Set array of options
     *
     * @param array $options
     * @return $this
     * @since 2.0.0
     */
    public function setOptions($options);

    /**
     * Get all options set
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptions();

    /**
     * Set an individual option
     *
     * @param string $option
     * @param mixed $value
     * @return $this
     * @since 2.0.0
     */
    public function setOption($option, $value);

    /**
     * Get an individual option
     *
     * @param string $option
     * @return mixed
     * @since 2.0.0
     */
    public function getOption($option);

    /**
     * Convert config to array
     *
     * @return array
     * @since 2.0.0
     */
    public function toArray();

    /**
     * Set session.name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Get session.name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set session.save_path
     *
     * @param string $savePath
     * @return $this
     * @since 2.0.0
     */
    public function setSavePath($savePath);

    /**
     * Set session.save_path
     *
     * @return string
     * @since 2.0.0
     */
    public function getSavePath();

    /**
     * Set session.cookie_lifetime
     *
     * @param int $cookieLifetime
     * @return $this
     * @since 2.0.0
     */
    public function setCookieLifetime($cookieLifetime);

    /**
     * Get session.cookie_lifetime
     *
     * @return int
     * @since 2.0.0
     */
    public function getCookieLifetime();

    /**
     * Set session.cookie_path
     *
     * @param string $cookiePath
     * @return $this
     * @since 2.0.0
     */
    public function setCookiePath($cookiePath);

    /**
     * Get session.cookie_path
     *
     * @return string
     * @since 2.0.0
     */
    public function getCookiePath();

    /**
     * Set session.cookie_domain
     *
     * @param string $cookieDomain
     * @return $this
     * @since 2.0.0
     */
    public function setCookieDomain($cookieDomain);

    /**
     * Get session.cookie_domain
     *
     * @return string
     * @since 2.0.0
     */
    public function getCookieDomain();

    /**
     * Set session.cookie_secure
     *
     * @param bool $cookieSecure
     * @return $this
     * @since 2.0.0
     */
    public function setCookieSecure($cookieSecure);

    /**
     * Get session.cookie_secure
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getCookieSecure();

    /**
     * Set session.cookie_httponly
     *
     * @param bool $cookieHttpOnly
     * @return $this
     * @since 2.0.0
     */
    public function setCookieHttpOnly($cookieHttpOnly);

    /**
     * Get session.cookie_httponly
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getCookieHttpOnly();

    /**
     * Set session.use_cookies
     *
     * @param bool $useCookies
     * @return $this
     * @since 2.0.0
     */
    public function setUseCookies($useCookies);

    /**
     * Get session.use_cookies
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getUseCookies();
}
