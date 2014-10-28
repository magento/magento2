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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model;

class Observer
{
    /**
     * @var Indexer\Category\Flat\State
     */
    protected $categoryFlatConfig;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $_catalogProduct;

    /**
     * Catalog category1
     *
     * @var \Magento\Catalog\Model\Resource\Category
     */
    protected $_categoryResource;

    /**
     * Factory for product resource
     *
     * @var \Magento\Catalog\Model\Resource\ProductFactory
     */
    protected $_productResourceFactory;

    /**
     * @param \Magento\Catalog\Model\Resource\Category $categoryResource
     * @param \Magento\Catalog\Model\Resource\Product $catalogProduct
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer\Category $catalogLayer
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Catalog\Model\Resource\ProductFactory $productResourceFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Category $categoryResource,
        \Magento\Catalog\Model\Resource\Product $catalogProduct,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer\Category $catalogLayer,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\Resource\ProductFactory $productResourceFactory
    ) {
        $this->_categoryResource = $categoryResource;
        $this->_catalogProduct = $catalogProduct;
        $this->_storeManager = $storeManager;
        $this->_catalogLayer = $catalogLayer;
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogData = $catalogData;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->_productResourceFactory = $productResourceFactory;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function catalogCheckIsUsingStaticUrlsAllowed(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $observer->getEvent()->getData('store_id');
        $result = $observer->getEvent()->getData('result');
        $result->isAllowed = $this->_catalogData->setStoreId($storeId)->isUsingStaticUrlsAllowed();
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function addCatalogToTopmenuItems(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $block->addIdentity(\Magento\Catalog\Model\Category::CACHE_TAG);
        $this->_addCategoriesToMenu($this->_catalogCategory->getStoreCategories(), $observer->getMenu(), $block);
    }

    /**
     * Recursively adds categories to top menu
     *
     * @param \Magento\Framework\Data\Tree\Node\Collection|array $categories
     * @param \Magento\Framework\Data\Tree\Node $parentCategoryNode
     * @param \Magento\Theme\Block\Html\Topmenu $block
     * @return void
     */
    protected function _addCategoriesToMenu($categories, $parentCategoryNode, $block)
    {
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $block->addIdentity(\Magento\Catalog\Model\Category::CACHE_TAG . '_' . $category->getId());

            $tree = $parentCategoryNode->getTree();
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                'url' => $this->_catalogCategory->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category)
            );
            $categoryNode = new \Magento\Framework\Data\Tree\Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode, $block);
        }
    }

    /**
     * Checks whether category belongs to active category's path
     *
     * @param \Magento\Framework\Data\Tree\Node $category
     * @return bool
     */
    protected function _isActiveMenuCategory($category)
    {
        if (!$this->_catalogLayer) {
            return false;
        }

        $currentCategory = $this->_catalogLayer->getCurrentCategory();
        if (!$currentCategory) {
            return false;
        }

        $categoryPathIds = explode(',', $currentCategory->getPathInStore());
        return in_array($category->getId(), $categoryPathIds);
    }
}
