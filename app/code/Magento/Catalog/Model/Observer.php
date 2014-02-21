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
     * @var \Magento\App\ReinitableConfigInterface
     */
    protected $_coreConfig;

    /**
     * Index indexer
     *
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexIndexer;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
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
     * Url factory
     *
     * @var \Magento\Catalog\Model\UrlFactory
     */
    protected $_urlFactory;

    /**
     * Factory for product resource
     *
     * @var \Magento\Catalog\Model\Resource\ProductFactory
     */
    protected $_productResourceFactory;

    /**
     * @param \Magento\Catalog\Model\UrlFactory $urlFactory
     * @param \Magento\Catalog\Model\Resource\Category $categoryResource
     * @param \Magento\Catalog\Model\Resource\Product $catalogProduct
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $catalogLayer
     * @param \Magento\Index\Model\Indexer $indexIndexer
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\App\ReinitableConfigInterface $coreConfig
     * @param \Magento\Catalog\Model\Resource\ProductFactory $productResourceFactory
     */
    public function __construct(
        \Magento\Catalog\Model\UrlFactory $urlFactory,
        \Magento\Catalog\Model\Resource\Category $categoryResource,
        \Magento\Catalog\Model\Resource\Product $catalogProduct,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $catalogLayer,
        \Magento\Index\Model\Indexer $indexIndexer,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\App\ReinitableConfigInterface $coreConfig,
        \Magento\Catalog\Model\Resource\ProductFactory $productResourceFactory
    ) {
        $this->_urlFactory = $urlFactory;
        $this->_categoryResource = $categoryResource;
        $this->_catalogProduct = $catalogProduct;
        $this->_storeManager = $storeManager;
        $this->_catalogLayer = $catalogLayer;
        $this->_indexIndexer = $indexIndexer;
        $this->_coreConfig = $coreConfig;
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogData = $catalogData;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->_productResourceFactory = $productResourceFactory;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function catalogCheckIsUsingStaticUrlsAllowed(\Magento\Event\Observer $observer)
    {
        $storeId = $observer->getEvent()->getData('store_id');
        $result  = $observer->getEvent()->getData('result');
        $result->isAllowed = $this->_catalogData->setStoreId($storeId)->isUsingStaticUrlsAllowed();
    }

    /**
     * Cron job method for product prices to reindex
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return void
     */
    public function reindexProductPrices(\Magento\Cron\Model\Schedule $schedule)
    {
        $indexProcess = $this->_indexIndexer->getProcessByCode('catalog_product_price');
        if ($indexProcess) {
            $indexProcess->reindexAll();
        }
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function addCatalogToTopmenuItems(\Magento\Event\Observer $observer)
    {
        $this->_addCategoriesToMenu(
            $this->_catalogCategory->getStoreCategories(),
            $observer->getMenu()
        );
    }

    /**
     * Recursively adds categories to top menu
     *
     * @param \Magento\Data\Tree\Node\Collection|array $categories
     * @param \Magento\Data\Tree\Node $parentCategoryNode
     * @return void
     */
    protected function _addCategoriesToMenu($categories, $parentCategoryNode)
    {
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $tree = $parentCategoryNode->getTree();
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                'url' => $this->_catalogCategory->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category)
            );
            $categoryNode = new \Magento\Data\Tree\Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            if ($this->categoryFlatConfig->isFlatEnabled()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode);
        }
    }

    /**
     * Checks whether category belongs to active category's path
     *
     * @param \Magento\Data\Tree\Node $category
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
