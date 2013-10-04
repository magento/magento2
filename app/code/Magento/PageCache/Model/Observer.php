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
 * @package     Magento_PageCache
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page cache observer model
 *
 * @category    Magento
 * @package     Magento_PageCache
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\PageCache\Model;

class Observer
{
    const XML_NODE_ALLOWED_CACHE = 'frontend/cache/allowed_requests';

    /**
     * Page cache data
     *
     * @var \Magento\PageCache\Helper\Data
     */
    protected $_pageCacheData = null;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_coreConfig;

    /**
     * @param \Magento\PageCache\Helper\Data $pageCacheData
     * @param \Magento\Core\Model\Config $coreConfig
     */
    public function __construct(
        \Magento\PageCache\Helper\Data $pageCacheData,
        \Magento\Core\Model\Config $coreConfig
    ) {
        $this->_pageCacheData = $pageCacheData;
        $this->_coreConfig = $coreConfig;
    }

    /**
     * Check if full page cache is enabled
     *
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->_pageCacheData->isEnabled();
    }

    /**
     * Check when cache should be disabled
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\PageCache\Model\Observer
     */
    public function processPreDispatch(\Magento\Event\Observer $observer)
    {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $action = $observer->getEvent()->getControllerAction();
        $request = $action->getRequest();
        $needCaching = true;

        if ($request->isPost()) {
            $needCaching = false;
        }

        $configuration = $this->_coreConfig->getNode(self::XML_NODE_ALLOWED_CACHE);

        if (!$configuration) {
            $needCaching = false;
        }

        $configuration = $configuration->asArray();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        if (!isset($configuration[$module])) {
            $needCaching = false;
        }

        if (isset($configuration[$module]['controller']) && $configuration[$module]['controller'] != $controller) {
            $needCaching = false;
        }

        if (isset($configuration[$module]['action']) && $configuration[$module]['action'] != $action) {
            $needCaching = false;
        }

        if (!$needCaching) {
            $this->_pageCacheData->setNoCacheCookie();
        }

        return $this;
    }

    /**
     * Temporary disabling full page caching by setting bo-cache cookie
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\PageCache\Model\Observer
     */
    public function setNoCacheCookie(\Magento\Event\Observer $observer)
    {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $this->_pageCacheData->setNoCacheCookie(0)->lockNoCacheCookie();
        return $this;
    }

    /**
     * Activating full page cache aby deleting no-cache cookie
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\PageCache\Model\Observer
     */
    public function deleteNoCacheCookie(\Magento\Event\Observer $observer)
    {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $this->_pageCacheData->unlockNoCacheCookie()->removeNoCacheCookie();
        return $this;
    }
}
