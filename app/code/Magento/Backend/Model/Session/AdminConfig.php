<?php
/**
 * Backend Session configuration object
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
namespace Magento\Backend\Model\Session;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Filesystem;
use Magento\Framework\Session\Config;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Magento Backend session configuration
 *
 * @method Config setSaveHandler()
 */
class AdminConfig extends Config
{
    /**
     * Configuration for admin session name
     */
    const SESSION_NAME_ADMIN = 'admin';

    /**
     * @var FrontNameResolver
     */
    protected $_frontNameResolver;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\String $stringHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Filesystem $filesystem
     * @param DeploymentConfig $deploymentConfig
     * @param string $scopeType
     * @param FrontNameResolver $frontNameResolver
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param string $lifetimePath
     * @param string $sessionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ValidatorFactory $validatorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\String $stringHelper,
        \Magento\Framework\App\RequestInterface $request,
        Filesystem $filesystem,
        DeploymentConfig $deploymentConfig,
        $scopeType,
        FrontNameResolver $frontNameResolver,
        \Magento\Framework\StoreManagerInterface $storeManager,
        $lifetimePath = self::XML_PATH_COOKIE_LIFETIME,
        $sessionName = self::SESSION_NAME_ADMIN
    ) {
        parent::__construct(
            $validatorFactory,
            $scopeConfig,
            $stringHelper,
            $request,
            $filesystem,
            $deploymentConfig,
            $scopeType,
            $lifetimePath
        );

        $this->_frontNameResolver = $frontNameResolver;
        $this->_storeManager = $storeManager;
        $adminPath = $this->extractAdminPath();
        $this->setCookiePath($adminPath);
        $this->setName($sessionName);
    }

    /**
     * Determine the admin path
     *
     * @return string
     */
    private function extractAdminPath()
    {
        $parsedUrl = parse_url($this->_storeManager->getStore()->getBaseUrl());
        $baseUrl = $parsedUrl['path'];
        $adminPath = $this->_frontNameResolver->getFrontName();

        return $baseUrl . $adminPath;
    }
}
