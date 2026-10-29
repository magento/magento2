<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Category;

use Magento\Catalog\Block\Breadcrumbs;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\ObjectManager;
use Magento\Cms\Block\Block;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Category View Block class
 * @api
 * @since 100.0.2
 */
class View extends Template implements IdentityInterface
{
    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Layer
     */
    protected $_catalogLayer;

    /**
     * @var CategoryHelper
     */
    protected $_categoryHelper;

    /**
     * @var Data|null
     */
    private $catalogData;

    /**
     * @param Context $context
     * @param Resolver $layerResolver
     * @param Registry $registry
     * @param CategoryHelper $categoryHelper
     * @param array $data
     * @param Data|null $catalogData
     */
    public function __construct(
        Context        $context,
        Resolver       $layerResolver,
        Registry       $registry,
        CategoryHelper $categoryHelper,
        array          $data = [],
        ?Data          $catalogData = null
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        $this->catalogData = $catalogData ?? ObjectManager::getInstance()
            ->get(Data::class);
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $block = $this->getLayout()->createBlock(Breadcrumbs::class);

        $category = $this->getCurrentCategory();
        if ($category) {
            $title = $category->getMetaTitle();
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            } else {
                $title = [];
                foreach ($this->catalogData->getBreadcrumbPath() as $breadcrumb) {
                    $title[] = $breadcrumb['label'];
                }
                $this->pageConfig->getTitle()->set(join($block->getTitleSeparator(), array_reverse($title)));
            }
            $description = $category->getMetaDescription();
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $category->getMetaKeywords();
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            if ($this->_categoryHelper->canUseCanonicalTag()) {
                $this->pageConfig->addRemotePageAsset(
                    $this->_categoryHelper->getCanonicalUrl($category->getUrl()),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($this->getCurrentCategory()->getName());
            }
        }

        return $this;
    }

    /**
     * Return Product list html
     *
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    /**
     * Retrieve current category model object
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * Return CMS block html
     *
     * @return mixed
     */
    public function getCmsBlockHtml()
    {
        if (!$this->getData('cms_block_html')) {
            $html = $this->getLayout()->createBlock(
                Block::class
            )->setBlockId(
                $this->getCurrentCategory()->getLandingPage()
            )->toHtml();
            $this->setData('cms_block_html', $html);
        }
        return $this->getData('cms_block_html');
    }

    /**
     * Check if category display mode is "Products Only"
     *
     * @return bool
     */
    public function isProductMode()
    {
        return $this->getCurrentCategory()->getDisplayMode() == Category::DM_PRODUCT;
    }

    /**
     * Check if category display mode is "Static Block and Products"
     *
     * @return bool
     */
    public function isMixedMode()
    {
        return $this->getCurrentCategory()->getDisplayMode() == Category::DM_MIXED;
    }

    /**
     * Check if category display mode is "Static Block Only"
     *
     * For anchor category with applied filter Static Block Only mode not allowed
     *
     * @return bool
     */
    public function isContentMode()
    {
        $category = $this->getCurrentCategory();
        $res = false;
        if ($category->getDisplayMode() == Category::DM_PAGE) {
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

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getCurrentCategory()->getIdentities();
    }
}
