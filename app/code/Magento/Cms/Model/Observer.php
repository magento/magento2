<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

/**
 * CMS Observer model
 */
class Observer
{
    /**
     * Cms page
     *
     * @var \Magento\Cms\Helper\Page
     */
    protected $_cmsPage;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Cms\Helper\Page $cmsPage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Cms\Helper\Page $cmsPage,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_cmsPage = $cmsPage;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Modify No Route Forward object
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function noRoute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getEvent()->getStatus()->setLoaded(
            true
        )->setForwardModule(
            'cms'
        )->setForwardController(
            'index'
        )->setForwardAction(
            'noroute'
        );
        return $this;
    }

    /**
     * Modify no Cookies forward object
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function noCookies(\Magento\Framework\Event\Observer $observer)
    {
        $redirect = $observer->getEvent()->getRedirect();

        $pageId = $this->_scopeConfig->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_NO_COOKIES_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pageUrl = $this->_cmsPage->getPageUrl($pageId);

        if ($pageUrl) {
            $redirect->setRedirectUrl($pageUrl);
        } else {
            $redirect->setRedirect(true)->setPath('cms/index/noCookies')->setArguments([]);
        }
        return $this;
    }
}
