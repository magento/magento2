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
namespace Magento\Cms\Helper;

use Magento\App\Action\Action;

/**
 * CMS Page Helper
 *
 * @category   Magento
 * @package    Magento_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Page extends \Magento\App\Helper\AbstractHelper
{
    const XML_PATH_NO_ROUTE_PAGE        = 'web/default/cms_no_route';
    const XML_PATH_NO_COOKIES_PAGE      = 'web/default/cms_no_cookies';
    const XML_PATH_HOME_PAGE            = 'web/default/cms_home_page';

    /**
     * Catalog product
     *
     * @var \Magento\Theme\Helper\Layout
     */
    protected $_pageLayout;

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
     * @var \Magento\Message\ManagerInterface
     */
    protected $messageManager;

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
     * @var \Magento\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\App\ViewInterface
     */
    protected $_view;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Message\ManagerInterface $messageManager
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Theme\Helper\Layout $pageLayout
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Escaper $escaper
     * @param \Magento\App\ViewInterface $view
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Message\ManagerInterface $messageManager,
        \Magento\Cms\Model\Page $page,
        \Magento\Theme\Helper\Layout $pageLayout,
        \Magento\View\DesignInterface $design,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Escaper $escaper,
        \Magento\App\ViewInterface $view
    ) {
        $this->messageManager = $messageManager;
        $this->_view = $view;
        $this->_page = $page;
        $this->_pageLayout = $pageLayout;
        $this->_design = $design;
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Renders CMS page on front end
     *
     * Call from controller action
     *
     * @param Action $action
     * @param int $pageId
     * @return bool
     */
    public function renderPage(Action $action, $pageId = null)
    {
        return $this->_renderPage($action, $pageId);
    }

    /**
     * Renders CMS page
     *
     * @param Action $action
     * @param int $pageId
     * @param bool $renderLayout
     * @return bool
     */
    protected function _renderPage(Action  $action, $pageId = null, $renderLayout = true)
    {
        if (!is_null($pageId) && $pageId!==$this->_page->getId()) {
            $delimiterPosition = strrpos($pageId, '|');
            if ($delimiterPosition) {
                $pageId = substr($pageId, 0, $delimiterPosition);
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
        $this->_view->getLayout()->getUpdate()->addHandle('default')->addHandle('cms_page_view');
        $this->_view->addPageLayoutHandles(array('id' => $this->_page->getIdentifier()));

        $this->_view->addActionLayoutHandles();
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

        $this->_view->loadLayoutUpdates();
        $layoutUpdate = ($this->_page->getCustomLayoutUpdateXml() && $inRange)
            ? $this->_page->getCustomLayoutUpdateXml() : $this->_page->getLayoutUpdateXml();
        if (!empty($layoutUpdate)) {
            $this->_view->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        }
        $this->_view->generateLayoutXml()->generateLayoutBlocks();

        $contentHeadingBlock = $this->_view->getLayout()->getBlock('page_content_heading');
        if ($contentHeadingBlock) {
            $contentHeading = $this->_escaper->escapeHtml($this->_page->getContentHeading());
            $contentHeadingBlock->setContentHeading($contentHeading);
        }

        if ($this->_page->getRootTemplate()) {
            $this->_pageLayout->applyTemplate($this->_page->getRootTemplate());
        }

        /* @TODO: Move catalog and checkout storage types to appropriate modules */
        $messageBlock = $this->_view->getLayout()->getMessagesBlock();
        $messageBlock->addStorageType($this->messageManager->getDefaultGroup());
        $messageBlock->addMessages(
            $this->messageManager->getMessages(true)
        );

        if ($renderLayout) {
            $this->_view->renderLayout();
        }

        return true;
    }

    /**
     * Renders CMS Page with more flexibility then original renderPage function.
     * Allows to use also backend action as first parameter.
     * Also takes third parameter which allows not run renderLayout method.
     *
     * @param Action $action
     * @param int $pageId
     * @param bool $renderLayout
     * @return bool
     */
    public function renderPageExtended(Action $action, $pageId = null, $renderLayout = true)
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

        return $this->_urlBuilder->getUrl(null, array('_direct' => $page->getIdentifier()));
    }
}
