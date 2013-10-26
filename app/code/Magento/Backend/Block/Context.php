<?php

namespace Magento\Backend\Block;

/**
 * Backend block context
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Context extends \Magento\Core\Block\Context
{
    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\Core\Model\Translate $translator
     * @param \Magento\Core\Model\CacheInterface $cache
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Core\Model\Session\AbstractSession $session
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\App\FrontController $frontController
     * @param \Magento\Core\Model\Factory\Helper $helperFactory
     * @param \Magento\Core\Model\View\Url $viewUrl
     * @param \Magento\View\ConfigInterface $viewConfig
     * @param \Magento\Core\Model\Cache\StateInterface $cacheState
     * @param \Magento\AuthorizationInterface $authorization
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Core\Model\App $app
     * @param array $data
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\View\LayoutInterface $layout,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\UrlInterface $urlBuilder,
        \Magento\Core\Model\Translate $translator,
        \Magento\Core\Model\CacheInterface $cache,
        \Magento\View\DesignInterface $design,
        \Magento\Core\Model\Session\AbstractSession $session,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\App\FrontController $frontController,
        \Magento\Core\Model\Factory\Helper $helperFactory,
        \Magento\Core\Model\View\Url $viewUrl,
        \Magento\View\ConfigInterface $viewConfig,
        \Magento\Core\Model\Cache\StateInterface $cacheState,
        \Magento\AuthorizationInterface $authorization,
        \Magento\Core\Model\Logger $logger,
        \Magento\Core\Model\App $app,
        array $data = array()
    ) {
        $this->_authorization = $authorization;
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $design,
            $session, $storeConfig, $frontController, $helperFactory, $viewUrl, $viewConfig, $cacheState, $logger, $app,
            $data
        );
    }

    /**
     * Retrieve Authorization
     *
     * @return \Magento\AuthorizationInterface
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }
}
