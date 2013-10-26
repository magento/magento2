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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

namespace Magento\Backend\Controller\Router;

/**
 * Class \Magento\Backend\Controller\Router\DefaultRouter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class DefaultRouter extends \Magento\Core\Controller\Varien\Router\Base
{
    /**
     * List of required request parameters
     * Order sensitive
     * @var array
     */
    protected $_requiredParams = array(
        'areaFrontName',
        'moduleFrontName',
        'controllerName',
        'actionName',
    );

    /**
     * Url key of area
     *
     * @var string
     */
    protected $_areaFrontName;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * @param \Magento\Backend\Helper\Data $backendData
     * Default routeId for router
     *
     * @var string
     */
    protected $_defaultRouteId;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Url
     */
    protected $_url;

    /**
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\App\ActionFactory $controllerFactory
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\App $app
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Route\Config $routeConfig
     * @param \Magento\Core\Model\Url\SecurityInfoInterface $securityInfo
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Url $url
     * @param \Magento\App\State $appState
     * @param $areaCode
     * @param $baseController
     * @param $routerId
     * @param $defaultRouteId
     *
     * @throws \InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Helper\Data $backendData,
        \Magento\App\ActionFactory $controllerFactory,
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\App $app,
        \Magento\App\State $appState,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Route\Config $routeConfig,
        \Magento\Core\Model\Url\SecurityInfoInterface $securityInfo,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Url $url,
        \Magento\App\State $appState,
        $areaCode,
        $baseController,
        $routerId,
        $defaultRouteId
    ) {
        parent::__construct(
            $controllerFactory,
            $filesystem,
            $app,
            $coreStoreConfig,
            $routeConfig,
            $securityInfo,
            $config,
            $url,
            $storeManager,
            $appState,
            $areaCode,
            $baseController,
            $routerId,
            $defaultRouteId
        );
        $this->_backendData = $backendData;
        $this->_appState = $appState;
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_areaFrontName = $this->_backendData->getAreaFrontName();
        $this->_defaultRouteId = $defaultRouteId;
    }

    /**
     * Fetch default path
     */
    public function fetchDefault()
    {
        // set defaults
        $pathParts = explode('/', $this->_getDefaultPath());
        $backendRoutes = $this->_getRoutes();
        $moduleFrontName = $backendRoutes[$this->_defaultRouteId]['frontName'];

        $this->getFront()->setDefault(array(
            'area'       => $this->_getParamWithDefaultValue($pathParts, 0, ''),
            'module'     => $this->_getParamWithDefaultValue($pathParts, 1, $moduleFrontName),
            'controller' => $this->_getParamWithDefaultValue($pathParts, 2, 'index'),
            'action'     => $this->_getParamWithDefaultValue($pathParts, 3, 'index'),
        ));
    }

    /**
     * Retrieve array param by key, or default value
     *
     * @param array $array
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function _getParamWithDefaultValue($array, $key, $defaultValue)
    {
        return !empty($array[$key]) ? $array[$key] : $defaultValue;
    }

    /**
     * Get router default request path
     * @return string
     */
    protected function _getDefaultPath()
    {
        return (string)$this->_config->getValue('web/default/admin', 'default');
    }

    /**
     * Dummy call to pass through checking
     *
     * @return boolean
     */
    protected function _beforeModuleMatch()
    {
        return true;
    }

    /**
     * checking if we installed or not and doing redirect
     *
     * @return bool
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function _afterModuleMatch()
    {
        if (!$this->_appState->isInstalled()) {
            $this->_app->getFrontController()
                ->getResponse()
                ->setRedirect($this->_url->getUrl('install'))
                ->sendResponse();
            exit;
        }
        return true;
    }

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return true;
    }

    /**
     * Check whether URL for corresponding path should use https protocol
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _shouldBeSecure($path)
    {
        return substr((string)$this->_config->getValue('web/unsecure/base_url', 'default'), 0, 5) === 'https'
            || $this->_coreStoreConfig->getConfigFlag(
                'web/secure/use_in_adminhtml',
                \Magento\Core\Model\AppInterface::ADMIN_STORE_ID
            ) && substr((string)$this->_config->getValue('web/secure/base_url', 'default'), 0, 5) === 'https';
    }

    /**
     * Retrieve current secure url
     *
     * @param \Magento\App\RequestInterface $request
     * @return string
     */
    protected function _getCurrentSecureUrl($request)
    {
        return $this->_storeManager->getStore(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
            ->getBaseUrl('link', true) . ltrim($request->getPathInfo(), '/');
    }

    /**
     * Check whether redirect should be used for secure routes
     *
     * @return bool
     */
    protected function _shouldRedirectToSecure()
    {
        return false;
    }

    /**
     * Build controller class name based on moduleName and controllerName
     *
     * @param string $realModule
     * @param string $controller
     * @return string
     */
    public function getControllerClassName($realModule, $controller)
    {
        /**
         * Start temporary block
         * TODO: Sprint#27. Delete after adminhtml refactoring
         */
        if ($realModule == 'Magento_Adminhtml') {
            return parent::getControllerClassName($realModule, $controller);
        }
        /**
         * End temporary block
         */

        $parts = explode('_', $realModule);
        $realModule = implode(\Magento\Autoload\IncludePath::NS_SEPARATOR, array_splice($parts, 0, 2));
        return $realModule . \Magento\Autoload\IncludePath::NS_SEPARATOR . 'Controller' .
            \Magento\Autoload\IncludePath::NS_SEPARATOR . ucfirst($this->_areaCode) .
            \Magento\Autoload\IncludePath::NS_SEPARATOR .
            str_replace('_', '\\', uc_words(str_replace('_', ' ', $controller)));

    }

    /**
     * Check whether this router should process given request
     *
     * @param array $params
     * @return bool
     */
    protected function _canProcess(array $params)
    {
        return $params['areaFrontName'] == $this->_areaFrontName;
    }
}
