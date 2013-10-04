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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Observer
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model;

class Observer
{
    /**
     * Catalog category flat
     *
     * @var \Magento\Catalog\Helper\Category\Flat
     */
    protected $_catalogCategoryFlat = null;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory = null;
    
    /**
     * @var \Magento\Core\Model\Config
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
     * Factory for category flat resource
     *
     * @var \Magento\Catalog\Model\Resource\Category\FlatFactory
     */
    protected $_flatResourceFactory;

    /**
     * Factory for product resource
     *
     * @var \Magento\Catalog\Model\Resource\ProductFactory
     */
    protected $_productResourceFactory;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\UrlFactory $urlFactory
     * @param \Magento\Catalog\Model\Resource\Category $categoryResource
     * @param \Magento\Catalog\Model\Resource\Product $catalogProduct
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $catalogLayer
     * @param \Magento\Index\Model\Indexer $indexIndexer
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Helper\Category\Flat $catalogCategoryFlat
     * @param \Magento\Core\Model\Config $coreConfig
     * @param \Magento\Catalog\Model\Resource\Category\FlatFactory $flatResourceFactory
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
        \Magento\Catalog\Helper\Category\Flat $catalogCategoryFlat,
        \Magento\Core\Model\Config $coreConfig,
        \Magento\Catalog\Model\Resource\Category\FlatFactory $flatResourceFactory,
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
        $this->_catalogCategoryFlat = $catalogCategoryFlat;
        $this->_flatResourceFactory = $flatResourceFactory;
        $this->_productResourceFactory = $productResourceFactory;
    }

    /**
     * Process catalog ata related with store data changes
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Catalog\Model\Observer
     */
    public function storeEdit(\Magento\Event\Observer $observer)
    {
        /** @var $store \Magento\Core\Model\Store */
        $store = $observer->getEvent()->getStore();
        if ($store->dataHasChangedFor('group_id')) {
            $this->_storeManager->reinitStores();
            /** @var $categoryFlatHelper \Magento\Catalog\Helper\Category\Flat */
            $categoryFlatHelper = $this->_catalogCategoryFlat;
            if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
                $this->_flatResourceFactory->create()
                    ->synchronize(null, array($store->getId()));
            }
            $this->_catalogProduct->refreshEnabledIndex($store);
        }
        return $this;
    }

    /**
     * Process catalog data related with new store
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Catalog\Model\Observer
     */
    public function storeAdd(\Magento\Event\Observer $observer)
    {
        /* @var $store \Magento\Core\Model\Store */
        $store = $observer->getEvent()->getStore();
        $this->_storeManager->reinitStores();
        $this->_coreConfig->reinit();
        /** @var $categoryFlatHelper \Magento\Catalog\Helper\Category\Flat */
        $categoryFlatHelper = $this->_catalogCategoryFlat;
        if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
            $this->_flatResourceFactory->create()
                ->synchronize(null, array($store->getId()));
        }
        $this->_productResourceFactory->create()->refreshEnabledIndex($store);
        return $this;
    }

    /**
     * Process catalog data related with store group root category
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Catalog\Model\Observer
     */
    public function storeGroupSave(\Magento\Event\Observer $observer)
    {
        /* @var $group \Magento\Core\Model\Store\Group */
        $group = $observer->getEvent()->getGroup();
        if ($group->dataHasChangedFor('root_category_id') || $group->dataHasChangedFor('website_id')) {
            $this->_storeManager->reinitStores();
            foreach ($group->getStores() as $store) {
                /** @var $categoryFlatHelper \Magento\Catalog\Helper\Category\Flat */
                $categoryFlatHelper = $this->_catalogCategoryFlat;
                if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
                    $this->_flatResourceFactory->create()
                        ->synchronize(null, array($store->getId()));
                }
            }
        }
        return $this;
    }

    /**
     * Process delete of store
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Observer
     */
    public function storeDelete(\Magento\Event\Observer $observer)
    {
        /** @var $categoryFlatHelper \Magento\Catalog\Helper\Category\Flat */
        $categoryFlatHelper = $this->_catalogCategoryFlat;
        if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
            $store = $observer->getEvent()->getStore();
            $this->_flatResourceFactory->create()->deleteStores($store->getId());
        }
        return $this;
    }

    /**
     * Process catalog data after category move
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Catalog\Model\Observer
     */
    public function categoryMove(\Magento\Event\Observer $observer)
    {
        $categoryId = $observer->getEvent()->getCategoryId();
        $prevParentId = $observer->getEvent()->getPrevParentId();
        $parentId = $observer->getEvent()->getParentId();
        /** @var $categoryFlatHelper \Magento\Catalog\Helper\Category\Flat */
        $categoryFlatHelper = $this->_catalogCategoryFlat;
        if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
            $this->_flatResourceFactory->create()
                ->move($categoryId, $prevParentId, $parentId);
        }
        return $this;
    }

    /**
     * Process catalog data after products import
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Catalog\Model\Observer
     */
    public function catalogProductImportAfter(\Magento\Event\Observer $observer)
    {
        $this->_urlFactory->create()->refreshRewrites();
        $this->_categoryResource->refreshProductIndex();
        return $this;
    }

    /**
     * After save event of category
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Observer
     */
    public function categorySaveAfter(\Magento\Event\Observer $observer)
    {
        /** @var $categoryFlatHelper \Magento\Catalog\Helper\Category\Flat */
        $categoryFlatHelper = $this->_catalogCategoryFlat;
        if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
            $category = $observer->getEvent()->getCategory();
            $this->_flatResourceFactory->create()->synchronize($category);
        }
        return $this;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Catalog\Model\Observer
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

            if ($this->_catalogCategoryFlat->isEnabled()) {
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

    /**
     * Change product type on the fly depending on selected options
     *
     * @param \Magento\Event\Observer $observer
     */
    public function transitionProductType(\Magento\Event\Observer $observer)
    {
        $switchableTypes = array(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\Catalog\Model\Product\Type::TYPE_CONFIGURABLE,
        );
        $product = $observer->getProduct();
        $attributes = $observer->getRequest()->getParam('attributes');
        if (!empty($attributes)) {
            $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_CONFIGURABLE);
        } elseif (in_array($product->getTypeId(), $switchableTypes)) {
            $product->setTypeInstance(null);
            $product->setTypeId($product->hasIsVirtual()
                ? \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
                : \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            );
        }
    }
}
