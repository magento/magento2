<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class ListProduct extends AbstractProduct implements IdentityInterface
{
    /**
     * Default toolbar block name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_defaultToolbarBlock = Toolbar::class;

    /**
     * Product Collection
     *
     * @var AbstractCollection
     * @since 2.0.0
     */
    protected $_productCollection;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     * @since 2.0.0
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     * @since 2.0.0
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     * @since 2.0.0
     */
    protected $categoryRepository;

    /**
     * @param Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Retrieve loaded product collection
     *
     * The goal of this method is to choose whether the existing collection should be returned
     * or a new one should be initialized.
     *
     * It is not just a caching logic, but also is a real logical check
     * because there are two ways how collection may be stored inside the block:
     *   - Product collection may be passed externally by 'setCollection' method
     *   - Product collection may be requested internally from the current Catalog Layer.
     *
     * And this method will return collection anyway,
     * even when it did not pass externally and therefore isn't cached yet
     *
     * @return AbstractCollection
     * @since 2.0.0
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->initializeProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * Get catalog layer model
     *
     * @return \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    public function getLayer()
    {
        return $this->_catalogLayer;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     * @since 2.0.0
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     * @since 2.0.0
     */
    public function getMode()
    {
        return $this->getChildBlock('toolbar')->getCurrentMode();
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $collection = $this->_getProductCollection();
        $this->configureToolbar($this->getToolbarBlock(), $collection);
        $collection->load();

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block
     *
     * @return Toolbar
     * @since 2.0.0
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     * @since 2.0.0
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     * @since 2.0.0
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * @param AbstractCollection $collection
     * @return $this
     * @since 2.0.0
     */
    public function setCollection($collection)
    {
        $this->_productCollection = $collection;
        return $this;
    }

    /**
     * @param array|string|integer|\Magento\Framework\App\Config\Element $code
     * @return $this
     * @since 2.0.0
     */
    public function addAttribute($code)
    {
        $this->_getProductCollection()->addAttributeToSelect($code);
        return $this;
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getPriceBlockTemplate()
    {
        return $this->_getData('price_block_template');
    }

    /**
     * Retrieve Catalog Config object
     *
     * @return \Magento\Catalog\Model\Config
     * @since 2.0.0
     */
    protected function _getConfig()
    {
        return $this->_catalogConfig;
    }

    /**
     * Prepare Sort By fields from Category Data
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Block\Product\ListProduct
     * @since 2.0.0
     */
    public function prepareSortableFieldsByCategory($category)
    {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($category->getAvailableSortByOptions());
        }
        $availableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            $categorySortBy = $this->getDefaultSortBy() ?: $category->getDefaultSortBy();
            if ($categorySortBy) {
                if (!$availableOrders) {
                    $availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
                }
                if (isset($availableOrders[$categorySortBy])) {
                    $this->setSortBy($categorySortBy);
                }
            }
        }

        return $this;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->_getProductCollection() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        $category = $this->getLayer()->getCurrentCategory();
        if ($category) {
            $identities[] = Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $category->getId();
        }
        return $identities;
    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @since 2.0.0
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @since 2.0.0
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    /**
     * Specifies that price rendering should be done for the list of products
     * i.e. rendering happens in the scope of product list, but not single product
     *
     * @return \Magento\Framework\Pricing\Render
     * @since 2.0.0
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }

    /**
     * Configures product collection from a layer and returns its instance.
     *
     * Also in the scope of a product collection configuration, this method initiates configuration of Toolbar.
     * The reason to do this is because we have a bunch of legacy code
     * where Toolbar configures several options of a collection and therefore this block depends on the Toolbar.
     *
     * This dependency leads to a situation where Toolbar sometimes called to configure a product collection,
     * and sometimes not.
     *
     * To unify this behavior and prevent potential bugs this dependency is explicitly called
     * when product collection initialized.
     *
     * @return Collection
     * @since 2.2.0
     */
    private function initializeProductCollection()
    {
        $layer = $this->getLayer();
        /* @var $layer \Magento\Catalog\Model\Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }

        // if this is a product view page
        if ($this->_coreRegistry->registry('product')) {
            // get collection of categories this product is associated with
            $categories = $this->_coreRegistry->registry('product')
                ->getCategoryCollection()->setPage(1, 1)
                ->load();
            // if the product is associated with any category
            if ($categories->count()) {
                // show products from this category
                $this->setCategoryId(current($categories->getIterator())->getId());
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }
        $collection = $layer->getProductCollection();

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $toolbar = $this->getToolbarBlock();
        $this->configureToolbar($toolbar, $collection);

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    /**
     * Configures the Toolbar block with options from this block and configured product collection.
     *
     * The purpose of this method is the one-way sharing of different sorting related data
     * between this block, which is responsible for product list rendering,
     * and the Toolbar block, whose responsibility is a rendering of these options.
     *
     * @param ProductList\Toolbar $toolbar
     * @param Collection $collection
     * @return void
     * @since 2.2.0
     */
    private function configureToolbar(Toolbar $toolbar, Collection $collection)
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }
}
