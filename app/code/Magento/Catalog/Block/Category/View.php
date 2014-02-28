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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category View block
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Category;

class View extends \Magento\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer $catalogLayer
     * @param \Magento\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer $catalogLayer,
        \Magento\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = array()
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $catalogLayer;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->createBlock('Magento\Catalog\Block\Breadcrumbs');

        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $category = $this->getCurrentCategory();
            $title = $category->getMetaTitle();
            if ($title) {
                $headBlock->setTitle($title);
            }
            $description = $category->getMetaDescription();
            if ($description) {
                $headBlock->setDescription($description);
            }
            $keywords = $category->getMetaKeywords();
            if ($keywords) {
                $headBlock->setKeywords($keywords);
            }
            //@todo: move canonical link to separate block
            if ($this->_categoryHelper->canUseCanonicalTag()
                && !$headBlock->getChildBlock('magento-page-head-category-canonical-link')
            ) {
                $headBlock->addChild(
                    'magento-page-head-category-canonical-link',
                    'Magento\Theme\Block\Html\Head\Link',
                    array(
                        'url' => $category->getUrl(),
                        'properties' => array('attributes' => array('rel' => 'canonical'))
                    )
                );
            }
            /**
             * want to show rss feed in the url
             */
            if ($this->isRssCatalogEnable() && $this->isTopCategory()) {
                $title = __('%1 RSS Feed', $this->getCurrentCategory()->getName());
                $headBlock->addRss($title, $this->getRssLink());
            }
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($this->getCurrentCategory()->getName());
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function isRssCatalogEnable()
    {
        return $this->_storeConfig->getConfig('rss/catalog/category');
    }

    /**
     * @return bool
     */
    public function isTopCategory()
    {
        return $this->getCurrentCategory()->getLevel()==2;
    }

    /**
     * @return string
     */
    public function getRssLink()
    {
        return $this->_urlBuilder->getUrl('rss/catalog/category', array(
            'cid' => $this->getCurrentCategory()->getId(),
            'store_id' => $this->_storeManager->getStore()->getId())
        );
    }

    /**
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * @return mixed
     */
    public function getCmsBlockHtml()
    {
        if (!$this->getData('cms_block_html')) {
            $html = $this->getLayout()->createBlock('Magento\Cms\Block\Block')
                ->setBlockId($this->getCurrentCategory()->getLandingPage())
                ->toHtml();
            $this->setData('cms_block_html', $html);
        }
        return $this->getData('cms_block_html');
    }

    /**
     * Check if category display mode is "Products Only"
     * @return bool
     */
    public function isProductMode()
    {
        return $this->getCurrentCategory()->getDisplayMode() == \Magento\Catalog\Model\Category::DM_PRODUCT;
    }

    /**
     * Check if category display mode is "Static Block and Products"
     * @return bool
     */
    public function isMixedMode()
    {
        return $this->getCurrentCategory()->getDisplayMode() == \Magento\Catalog\Model\Category::DM_MIXED;
    }

    /**
     * Check if category display mode is "Static Block Only"
     * For anchor category with applied filter Static Block Only mode not allowed
     *
     * @return bool
     */
    public function isContentMode()
    {
        $category = $this->getCurrentCategory();
        $res = false;
        if ($category->getDisplayMode() == \Magento\Catalog\Model\Category::DM_PAGE) {
            $res = true;
            if ($category->getIsAnchor()) {
                $state = $this->_catalogLayer->getState();
                if ($state && $state->getFilters()) {
                    $res = false;
                }
            }
        }
        return $res;
    }
}
