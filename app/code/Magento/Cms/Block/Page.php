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
namespace Magento\Cms\Block;

/**
 * Cms page content block
 */
class Page extends \Magento\View\Element\AbstractBlock
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

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
     * Construct
     *
     * @param \Magento\View\Element\Context $context
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Context $context,
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\PageFactory $pageFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        // used singleton (instead factory) because there exist dependencies on \Magento\Cms\Helper\Page
        $this->_page = $page;
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_pageFactory = $pageFactory;
    }

    /**
     * Retrieve Page instance
     *
     * @return \Magento\Cms\Model\Page
     */
    public function getPage()
    {
        if (!$this->hasData('page')) {
            if ($this->getPageId()) {
                /** @var \Magento\Cms\Model\Page $page */
                $page = $this->_pageFactory->create();
                $page->setStoreId($this->_storeManager->getStore()->getId())
                    ->load($this->getPageId(), 'identifier');
            } else {
                $page = $this->_page;
            }
            $this->setData('page', $page);
        }
        return $this->getData('page');
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $page = $this->getPage();

        // show breadcrumbs
        if ($this->_storeConfig->getConfig('web/default/show_cms_breadcrumbs')
            && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))
            && ($page->getIdentifier()!==$this->_storeConfig->getConfig('web/default/cms_home_page'))
            && ($page->getIdentifier()!==$this->_storeConfig->getConfig('web/default/cms_no_route'))) {
                $breadcrumbs->addCrumb('home', array('label'=>__('Home'), 'title'=>__('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()));
                $breadcrumbs->addCrumb('cms_page', array('label'=>$page->getTitle(), 'title'=>$page->getTitle()));
        }

        $root = $this->getLayout()->getBlock('root');
        if ($root) {
            $root->addBodyClass('cms-'.$page->getIdentifier());
        }

        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($page->getTitle());
            $head->setKeywords($page->getMetaKeywords());
            $head->setDescription($page->getMetaDescription());
        }

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            // Setting empty page title if content heading is absent
            $cmsTitle = $page->getContentHeading() ? : ' ';
            $pageMainTitle->setPageTitle($this->escapeHtml($cmsTitle));
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = $this->_filterProvider->getPageFilter()->filter($this->getPage()->getContent());
        $html = $this->getLayout()->renderElement('messages') . $html;
        return $html;
    }
}
