<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Config;

/**
 * Standard session configuration
 */
interface ConfigInterface
{
    public function setOptions($options);
    public function getOptions();

    public function setOption($option, $value);
    public function getOption($option);
    public function hasOption($option);

    public function toArray();

    public function setName($name);
    public function getName();

    public function setSavePath($savePath);
    public function getSavePath();

    public function setCookieLifetime($cookieLifetime);
    public function getCookieLifetime();

    public function setCookiePath($cookiePath);
    public function getCookiePath();

    public function setCookieDomain($cookieDomain);
    public function getCookieDomain();

    public function setCookieSecure($cookieSecure);
    public function getCookieSecure();

    public function setCookieHttpOnly($cookieHttpOnly);
    public function getCookieHttpOnly();

    public function setUseCookies($useCookies);
    public function getUseCookies();

    public function setRememberMeSeconds($rememberMeSeconds);
    public function getRememberMeSeconds();
}
