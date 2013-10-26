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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * CMS Page Helper
 *
 * @category   Magento
 * @package    Magento_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Cms\Helper;

class Page extends \Magento\Core\Helper\AbstractHelper
{
    const XML_PATH_NO_ROUTE_PAGE        = 'web/default/cms_no_route';
    const XML_PATH_NO_COOKIES_PAGE      = 'web/default/cms_no_cookies';
    const XML_PATH_HOME_PAGE            = 'web/default/cms_home_page';

    /**
     * Catalog product
     *
     * @var \Magento\Page\Helper\Layout
     */
    protected $_pageLayout;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Design package instance
     *
     * @var \Magento\View\DesignInterface
     */
    protected $_design;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * @var \Magento\Core\Model\Session\Pool
     */
    protected $_sessionPool;

    /**
     * Locale
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * Url
     *
     * @var \Magento\UrlInterface
     */
    protected $_url;

    /**
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\Session\Pool $sessionFactory
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Page\Helper\Layout $pageLayout
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\UrlInterface $url
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\Session\Pool $sessionFactory,
        \Magento\Cms\Model\Page $page,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Page\Helper\Layout $pageLayout,
        \Magento\View\DesignInterface $design,
        \Magento\UrlInterface $url,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\LocaleInterface $locale
    ) {
        $this->_sessionPool = $sessionFactory;
        // used singleton (instead factory) because there exist dependencies on \Magento\Cms\Helper\Page
        $this->_page = $page;
        $this->_eventManager = $eventManager;
        $this->_pageLayout = $pageLayout;
        $this->_design = $design;
        $this->_url = $url;
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
        parent::__construct($context);
    }

    /**
     * Renders CMS page on front end
     *
     * Call from controller action
     *
     * @param \Magento\Core\Controller\Front\Action $action
     * @param integer $pageId
     * @return boolean
     */
    public function renderPage(\Magento\Core\Controller\Front\Action $action, $pageId = null)
    {
        return $this->_renderPage($action, $pageId);
    }

    /**
     * Renders CMS page
     *
     * @param \Magento\Core\Controller\Front\Action|\Magento\Core\Controller\Varien\Action $action
     * @param integer $pageId
     * @param bool $renderLayout
     * @return boolean
     */
    protected function _renderPage(\Magento\Core\Controller\Varien\Action  $action, $pageId = null, $renderLayout = true)
    {
        if (!is_null($pageId) && $pageId!==$this->_page->getId()) {
            $delimeterPosition = strrpos($pageId, '|');
            if ($delimeterPosition) {
                $pageId = substr($pageId, 0, $delimeterPosition);
            }

            $this->_page->setStoreId($this->_storeManager->getStore()->getId());
            if (!$this->_page->load($pageId)) {
                return false;
            }
        }

        if (!$this->_page->getId()) {
            return false;
        }

        $inRange = $this->_locale->isStoreDateInInterval(null, $this->_page->getCustomThemeFrom(),
            $this->_page->getCustomThemeTo());

        if ($this->_page->getCustomTheme()) {
            if ($inRange) {
                $this->_design->setDesignTheme($this->_page->getCustomTheme());
            }
        }
        $action->addPageLayoutHandles(array('id' => $this->_page->getIdentifier()));

        $action->addActionLayoutHandles();
        if ($this->_page->getRootTemplate()) {
            $handle = ($this->_page->getCustomRootTemplate()
                        && $this->_page->getCustomRootTemplate() != 'empty'
                        && $inRange) ? $this->_page->getCustomRootTemplate() : $this->_page->getRootTemplate();
            $this->_pageLayout->applyHandle($handle);
        }

        $this->_eventManager->dispatch(
            'cms_page_render',
            array('page' => $this->_page, 'controller_action' => $action)
        );

        $action->loadLayoutUpdates();
        $layoutUpdate = ($this->_page->getCustomLayoutUpdateXml() && $inRange)
            ? $this->_page->getCustomLayoutUpdateXml() : $this->_page->getLayoutUpdateXml();
        if (!empty($layoutUpdate)) {
            $action->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        }
        $action->generateLayoutXml()->generateLayoutBlocks();

        $contentHeadingBlock = $action->getLayout()->getBlock('page_content_heading');
        if ($contentHeadingBlock) {
            $contentHeading = $this->escapeHtml($this->_page->getContentHeading());
            $contentHeadingBlock->setContentHeading($contentHeading);
        }

        if ($this->_page->getRootTemplate()) {
            $this->_pageLayout->applyTemplate($this->_page->getRootTemplate());
        }

        /* @TODO: Move catalog and checkout storage types to appropriate modules */
        $messageBlock = $action->getLayout()->getMessagesBlock();
        $sessions = array(
            'Magento\Catalog\Model\Session',
            'Magento\Checkout\Model\Session',
            'Magento\Customer\Model\Session'
        );
        foreach ($sessions as $storageType) {
            $storage = $this->_sessionPool->get($storageType);
            if ($storage) {
                $messageBlock->addStorageType($storageType);
                $messageBlock->addMessages($storage->getMessages(true));
            }
        }

        if ($renderLayout) {
            $action->renderLayout();
        }

        return true;
    }

    /**
     * Renders CMS Page with more flexibility then original renderPage function.
     * Allows to use also backend action as first parameter.
     * Also takes third parameter which allows not run renderLayout method.
     *
     * @param \Magento\Core\Controller\Varien\Action $action
     * @param $pageId
     * @param $renderLayout
     * @return bool
     */
    public function renderPageExtended(\Magento\Core\Controller\Varien\Action $action, $pageId = null, $renderLayout = true)
    {
        return $this->_renderPage($action, $pageId, $renderLayout);
    }

    /**
     * Retrieve page direct URL
     *
     * @param string $pageId
     * @return string
     */
    public function getPageUrl($pageId = null)
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->_pageFactory->create();
        if (!is_null($pageId) && $pageId !== $page->getId()) {
            $page->setStoreId($this->_storeManager->getStore()->getId());
            if (!$page->load($pageId)) {
                return null;
            }
        }

        if (!$page->getId()) {
            return null;
        }

        return $this->_url->getUrl(null, array('_direct' => $page->getIdentifier()));
    }
}
