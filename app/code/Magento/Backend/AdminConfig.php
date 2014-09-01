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
namespace Magento\Backend;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Session\Config;

/**
 * Magento Backend session configuration
 *
 * @method Config setSaveHandler()
 */
class AdminConfig extends Config
{
    /**
     * @var FrontNameResolver $frontNameResolver
     */
    protected $frontNameResolver;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\String $stringHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param string $scopeType
     * @param FrontNameResolver $frontNameResolver
     * @param string $saveMethod
     * @param null|string $savePath
     * @param null|string $cacheLimiter
     * @param string $lifetimePath
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\String $stringHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Filesystem $filesystem,
        $scopeType,
        FrontNameResolver $frontNameResolver,
        $saveMethod = \Magento\Framework\Session\SaveHandlerInterface::DEFAULT_HANDLER,
        $savePath = null,
        $cacheLimiter = null,
        $lifetimePath = self::XML_PATH_COOKIE_LIFETIME
    ) {
        parent::__construct(
            $scopeConfig,
            $stringHelper,
            $request,
            $appState,
            $filesystem,
            $scopeType,
            $saveMethod,
            $savePath,
            $cacheLimiter,
            $lifetimePath
        );

        $this->frontNameResolver = $frontNameResolver;

        $baseUrl = $this->_httpRequest->getBaseUrl();
        $adminPath = $this->extractAdminPath($baseUrl);
        $this->setCookiePath($adminPath);
    }

    /**
     * Determine the admin path
     *
     * @param string $baseUrl
     * @return string
     * @throws \InvalidArgumentException
     */
    private function extractAdminPath($baseUrl)
    {
        if (!is_string($baseUrl)) {
            throw new \InvalidArgumentException('Cookie path is not a string.');
        }

        $adminPath = $this->frontNameResolver->getFrontName();

        if (!substr($baseUrl, -1) || ('/' != substr($baseUrl, -1))) {
            $baseUrl = $baseUrl . '/';
        }

        return $baseUrl . $adminPath;
    }
}
