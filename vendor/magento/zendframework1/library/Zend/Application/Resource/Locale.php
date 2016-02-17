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
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
#require_once 'Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for initializing the locale
 *
 * @uses       Zend_Application_Resource_Base
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Application_Resource_Locale
    extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'Zend_Locale';

    /**
     * @var Zend_Locale
     */
    protected $_locale;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Locale
     */
    public function init()
    {
        return $this->getLocale();
    }

    /**
     * Retrieve locale object
     *
     * @return Zend_Locale
     */
    public function getLocale()
    {
        if (null === $this->_locale) {
            $options = $this->getOptions();

            if (!isset($options['default'])) {
                $this->_locale = new Zend_Locale();
            } elseif (!isset($options['force'])
                      || (bool)$options['force'] == false
            ) {
                // Don't force any locale, just go for auto detection
                Zend_Locale::setDefault($options['default']);
                $this->_locale = new Zend_Locale();
            } else {
                $this->_locale = new Zend_Locale($options['default']);
            }

            $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
                ? $options['registry_key']
                : self::DEFAULT_REGISTRY_KEY;
            Zend_Registry::set($key, $this->_locale);
        }

        return $this->_locale;
    }

    /**
     * Set the cache
     *
     * @param string|Zend_Cache_Core $cache
     * @return Zend_Application_Resource_Locale
     */
    public function setCache($cache)
    {
        if (is_string($cache)) {
            $bootstrap = $this->getBootstrap();
            if ($bootstrap instanceof Zend_Application_Bootstrap_ResourceBootstrapper
                && $bootstrap->hasPluginResource('CacheManager')
            ) {
                $cacheManager = $bootstrap->bootstrap('CacheManager')
                    ->getResource('CacheManager');
                if (null !== $cacheManager && $cacheManager->hasCache($cache)) {
                    $cache = $cacheManager->getCache($cache);
                }
            }
        }

        if ($cache instanceof Zend_Cache_Core) {
            Zend_Locale::setCache($cache);
        }

        return $this;
    }
}
