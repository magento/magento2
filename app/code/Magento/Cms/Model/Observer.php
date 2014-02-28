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
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @param \Magento\Cms\Helper\Page $cmsPage
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     */
    public function __construct(
        \Magento\Cms\Helper\Page $cmsPage,
        \Magento\Core\Model\Store\Config $coreStoreConfig
    ) {
        $this->_cmsPage = $cmsPage;
        $this->_coreStoreConfig = $coreStoreConfig;
    }

    /**
     * Modify No Route Forward object
     *
     * @param \Magento\Event\Observer $observer
     * @return $this
     */
    public function noRoute(\Magento\Event\Observer $observer)
    {
        $observer->getEvent()->getStatus()
            ->setLoaded(true)
            ->setForwardModule('cms')
            ->setForwardController('index')
            ->setForwardAction('noroute');
        return $this;
    }

    /**
     * Modify no Cookies forward object
     *
     * @param \Magento\Event\Observer $observer
     * @return $this
     */
    public function noCookies(\Magento\Event\Observer $observer)
    {
        $redirect = $observer->getEvent()->getRedirect();

        $pageId  = $this->_coreStoreConfig->getConfig(\Magento\Cms\Helper\Page::XML_PATH_NO_COOKIES_PAGE);
        $pageUrl = $this->_cmsPage->getPageUrl($pageId);

        if ($pageUrl) {
            $redirect->setRedirectUrl($pageUrl);
        } else {
            $redirect->setRedirect(true)
                ->setPath('cms/index/noCookies')
                ->setArguments(array());
        }
        return $this;
    }

}
