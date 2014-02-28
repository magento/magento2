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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model;

/**
 * Application model
 *
 * Application should have: areas, store, locale, translator, design package
 */
class App implements \Magento\AppInterface
{
    /**#@+
     * Product edition labels
     */
    const EDITION_COMMUNITY    = 'Community';
    const EDITION_ENTERPRISE   = 'Enterprise';
    /**#@-*/

    /**
     * Current Magento edition.
     *
     * @var string
     * @static
     */
    protected $_currentEdition = self::EDITION_COMMUNITY;

    /**
     * Magento version
     */
    const VERSION = '2.0.0.0-dev67';

    /**
     * Application run code
     */
    const PARAM_RUN_CODE = 'MAGE_RUN_CODE';

    /**
     * Application run type (store|website)
     */
    const PARAM_RUN_TYPE = 'MAGE_RUN_TYPE';

    /**
     * Disallow cache
     */
    const PARAM_BAN_CACHE = 'global_ban_use_cache';

    /**
     * Allowed modules
     */
    const PARAM_ALLOWED_MODULES = 'allowed_modules';

    /**
     * Caching params, that applied for all cache frontends regardless of type
     */
    const PARAM_CACHE_FORCED_OPTIONS = 'cache_options';

    /**
     * Application loaded areas array
     *
     * @var array
     */
    protected $_areas = array();

    /**
     * Application location object
     *
     * @var LocaleInterface
     */
    protected $_locale;

    /**
     * Application configuration object
     *
     * @var \Magento\App\ConfigInterface
     */
    protected $_config;

    /**
     * Application front controller
     *
     * @var \Magento\App\FrontControllerInterface
     */
    protected $_frontController;

    /**
     * Flag to identify whether front controller is initialized
     *
     * @var bool
     */
    protected $_isFrontControllerInitialized = false;

    /**
     * Cache object
     *
     * @var \Magento\App\CacheInterface
     */
    protected $_cache;

    /**
     * Request object
     *
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * Response object
     *
     * @var \Magento\App\ResponseInterface
     */
    protected $_response;

    /**
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Data base updater object
     *
     * @var \Magento\Module\UpdaterInterface
     */
    protected $_dbUpdater;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Config\Scope
     */
    protected $_configScope;

    /**
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\App\CacheInterface $cache
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\App\State $appState
     * @param \Magento\Config\Scope $configScope
     * @param \Magento\App\FrontControllerInterface $frontController
     */
    public function __construct(
        \Magento\App\ConfigInterface $config,
        \Magento\App\CacheInterface $cache,
        \Magento\ObjectManager $objectManager,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\App\State $appState,
        \Magento\Config\Scope $configScope,
        \Magento\App\FrontControllerInterface $frontController
    ) {
        $this->_config = $config;
        $this->_cache = $cache;
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->_eventManager = $eventManager;
        $this->_configScope = $configScope;
        $this->_frontController = $frontController;
    }

    /**
     * Throw an exception, if the application has not been installed yet
     *
     * @return void
     * @throws \Magento\Exception
     */
    public function requireInstalledInstance()
    {
        if (false == $this->_appState->isInstalled()) {
            throw new \Magento\Exception('Application is not installed yet, please complete the installation first.');
        }
    }

    /**
     * Retrieve cookie object
     *
     * @return \Magento\Stdlib\Cookie
     */
    public function getCookie()
    {
        return $this->_objectManager->get('Magento\Stdlib\Cookie');
    }

    /**
     * Re-declare custom error handler
     *
     * @param   string $handler
     * @return  $this
     */
    public function setErrorHandler($handler)
    {
        set_error_handler($handler);
        return $this;
    }

    /**
     * Loading part of area data
     *
     * @param   string $area
     * @param   string $part
     * @return  $this
     */
    public function loadAreaPart($area, $part)
    {
        $this->getArea($area)->load($part);
        return $this;
    }

    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  \Magento\Core\Model\App\Area
     */
    public function getArea($code)
    {
        if (!isset($this->_areas[$code])) {
            $this->_areas[$code] = $this->_objectManager->create(
                'Magento\Core\Model\App\Area',
                array('areaCode' => $code)
            );
        }
        return $this->_areas[$code];
    }

    /**
     * Get distro locale code
     *
     * @return string
     */
    public function getDistroLocaleCode()
    {
        return self::DISTRO_LOCALE_CODE;
    }

    /**
     * Retrieve application locale object
     *
     * @return LocaleInterface
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            $this->_locale = $this->_objectManager->get('Magento\Core\Model\LocaleInterface');
        }
        return $this->_locale;
    }

    /**
     * Retrieve layout object
     *
     * @return \Magento\View\LayoutInterface
     */
    public function getLayout()
    {
        return $this->_objectManager->get('Magento\View\LayoutInterface');
    }

    /**
     * Retrieve application base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE, 'default');
    }

    /**
     * Retrieve configuration object
     *
     * @return \Magento\App\ConfigInterface
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Retrieve front controller object
     *
     * @return \Magento\App\FrontController
     */
    public function getFrontController()
    {
        return $this->_frontController;
    }

    /**
     * Get core cache model
     *
     * @return \Magento\App\CacheInterface
     */
    public function getCacheInstance()
    {
        return $this->_cache;
    }

    /**
     * Retrieve cache object
     *
     * @return \Magento\Cache\FrontendInterface
     */
    public function getCache()
    {
        return $this->_cache->getFrontend();
    }

    /**
     * Loading cache data
     *
     * @param   string $cacheId
     * @return  string
     */
    public function loadCache($cacheId)
    {
        return $this->_cache->load($cacheId);
    }

    /**
     * Saving cache data
     *
     * @param mixed $data
     * @param string $cacheId
     * @param array $tags
     * @param bool $lifeTime
     * @return $this
     */
    public function saveCache($data, $cacheId, $tags = array(), $lifeTime = false)
    {
        $this->_cache->save($data, $cacheId, $tags, $lifeTime);
        return $this;
    }

    /**
     * Remove cache
     *
     * @param   string $cacheId
     * @return  $this
     */
    public function removeCache($cacheId)
    {
        $this->_cache->remove($cacheId);
        return $this;
    }

    /**
     * Cleaning cache
     *
     * @param   array $tags
     * @return  $this
     */
    public function cleanCache($tags = array())
    {
        $this->_cache->clean($tags);
        return $this;
    }

    /**
     * Deletes all session files
     *
     * @return $this
     */
    public function cleanAllSessions()
    {
        if (session_module_name() == 'files') {
            /** @var \Magento\App\Filesystem $filesystem */
            $filesystem = $this->_objectManager->create('Magento\App\Filesystem');
            $sessionDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::SESSION_DIR);
            foreach ($sessionDirectory->read() as $path) {
                $sessionDirectory->delete($path);
            }
        }
        return $this;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = $this->_objectManager->get('Magento\App\RequestInterface');
        }
        return $this->_request;
    }

    /**
     * Request setter
     *
     * @param \Magento\App\RequestInterface $request
     * @return $this
     */
    public function setRequest(\Magento\App\RequestInterface $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\App\ResponseInterface
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = $this->_objectManager->get('Magento\App\ResponseInterface');
            $this->_response->setHeader('Content-Type', 'text/html; charset=UTF-8');
        }
        return $this->_response;
    }

    /**
     * Response setter
     *
     * @param \Magento\App\ResponseInterface $response
     * @return $this
     */
    public function setResponse(\Magento\App\ResponseInterface $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Check if developer mode is enabled
     *
     * @return bool
     */
    public function isDeveloperMode()
    {
        return $this->_appState->getMode() == \Magento\App\State::MODE_DEVELOPER;
    }

    /**
     * Get current Magento edition
     *
     * @static
     * @return string
     */
    public function getEdition()
    {
        return $this->_currentEdition;
    }

    /**
     * Set edition
     *
     * @param string $edition
     * @return void
     */
    public function setEdition($edition)
    {
        $this->_currentEdition = $edition;
    }

    /**
     * Gets the current Magento version string
     * @link http://www.magentocommerce.com/blog/new-community-edition-release-process/
     *
     * @return string
     */
    public function getVersion()
    {
        $info = $this->getVersionInfo();
        return trim("{$info['major']}.{$info['minor']}.{$info['revision']}"
            . ($info['patch'] != '' ? ".{$info['patch']}" : "")
            . "-{$info['stability']}{$info['number']}", '.-');
    }

    /**
     * Gets the detailed Magento version information
     * @link http://www.magentocommerce.com/blog/new-community-edition-release-process/
     *
     * @return array
     */
    public function getVersionInfo()
    {
        return array(
            'major'     => '2',
            'minor'     => '0',
            'revision'  => '0',
            'patch'     => '0',
            'stability' => 'dev',
            'number'    => '67',
        );
    }
}
