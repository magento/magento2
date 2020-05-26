<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item\RelatedProducts;

/**
 * Cart crosssell list
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Crosssell extends AbstractProduct
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 4;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var StockHelper
     */
    protected $stockHelper;

    /**
     * @var LinkFactory
     */
    protected $_productLinkFactory;

    /**
     * @var RelatedProducts
     */
    protected $_itemRelationsList;
    /**
     * @var CollectionFactory|null
     */
    private $productCollectionFactory;
    /**
     * @var ProductRepositoryInterface|null
     */
    private $productRepository;
    /**
     * @var Product[]
     */
    private $cartProducts;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Visibility $productVisibility
     * @param LinkFactory $productLinkFactory
     * @param RelatedProducts $itemRelationsList
     * @param StockHelper $stockHelper
     * @param array $data
     * @param CollectionFactory|null $productCollectionFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Visibility $productVisibility,
        LinkFactory $productLinkFactory,
        RelatedProducts $itemRelationsList,
        StockHelper $stockHelper,
        array $data = [],
        ?CollectionFactory $productCollectionFactory = null,
        ?ProductRepositoryInterface $productRepository = null
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_productVisibility = $productVisibility;
        $this->_productLinkFactory = $productLinkFactory;
        $this->_itemRelationsList = $itemRelationsList;
        $this->stockHelper = $stockHelper;
        parent::__construct(
            $context,
            $data
        );
        $this->_isScopePrivate = true;
        $this->productCollectionFactory = $productCollectionFactory
            ?? ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->productRepository = $productRepository
            ?? ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
    }

    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getItems()
    {
        $items = $this->getData('items');
        if ($items === null) {
            $items = [];
            $ninProductIds = $this->_getCartProductIds();
            if ($ninProductIds) {
                $lastAddedProduct = $this->getLastAddedProduct();
                if ($lastAddedProduct) {
                    $collection = $this->_getCollection()
                        ->addProductFilter($lastAddedProduct->getData($this->getProductLinkField()));
                    if (!empty($ninProductIds)) {
                        $collection->addExcludeProductFilter($ninProductIds);
                    }
                    $collection->setPositionOrder()->load();

                    foreach ($collection as $item) {
                        $ninProductIds[] = $item->getId();
                        $items[] = $item;
                    }
                }

                if (count($items) < $this->_maxItemCount) {
                    $filterProductIds = array_merge(
                        $this->getCartProductLinkIds(),
                        $this->getCartRelatedProductLinkIds()
                    );
                    $collection = $this->_getCollection()->addProductFilter(
                        $filterProductIds
                    )->addExcludeProductFilter(
                        $ninProductIds
                    )->setPageSize(
                        $this->_maxItemCount - count($items)
                    )->setGroupBy()->setPositionOrder()->load();
                    foreach ($collection as $item) {
                        $items[] = $item;
                    }
                }
            }

            $this->setData('items', $items);
        }
        return $items;
    }

    /**
     * Count items
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getItemCount()
    {
        return count($this->getItems());
    }

    /**
     * Get ids of products that are in cart
     *
     * @return array
     */
    protected function _getCartProductIds()
    {
        $ids = $this->getData('_cart_product_ids');
        if ($ids === null) {
            $ids = [];
            foreach ($this->getCartProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setData('_cart_product_ids', $ids);
        }
        return $ids;
    }

    /**
     * Get last product ID that was added to cart and remove this information from session
     *
     * @return int
     * @codeCoverageIgnore
     */
    protected function _getLastAddedProductId()
    {
        return $this->_checkoutSession->getLastAddedProductId(true);
    }

    /**
     * Get quote instance
     *
     * @return \Magento\Quote\Model\Quote
     * @codeCoverageIgnore
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Get crosssell products collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    protected function _getCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collection */
        $collection = $this->_productLinkFactory->create()->useCrossSellLinks()->getProductCollection()->setStoreId(
            $this->_storeManager->getStore()->getId()
        )->addStoreFilter()->setPageSize(
            $this->_maxItemCount
        )->setVisibility(
            $this->_productVisibility->getVisibleInCatalogIds()
        );
        $this->_addProductAttributesAndPrices($collection);

        return $collection;
    }

    /**
     * Get product link ID field
     *
     * @return string
     */
    private function getProductLinkField(): string
    {
        /* @var $collection Collection */
        $collection = $this->productCollectionFactory->create();
        return $collection->getProductEntityMetadata()->getLinkField();
    }

    /**
     * Get cart products link IDs
     *
     * @return array
     */
    private function getCartProductLinkIds(): array
    {
        $linkField = $this->getProductLinkField();
        $linkIds = [];
        foreach ($this->getCartProducts() as $product) {
            /** * @var Product $product */
            $linkIds[] = $product->getData($linkField);
        }
        return $linkIds;
    }

    /**
     * Get cart related products link IDs
     *
     * @return array
     */
    private function getCartRelatedProductLinkIds(): array
    {
        $productIds = $this->_itemRelationsList->getRelatedProductIds($this->getQuote()->getAllItems());
        $linkIds = [];
        if (!empty($productIds)) {
            $linkField = $this->getProductLinkField();
            /* @var $collection Collection */
            $collection = $this->productCollectionFactory->create();
            $collection->addIdFilter($productIds);
            foreach ($collection as $product) {
                /** * @var Product $product */
                $linkIds[] = $product->getData($linkField);
            }
        }
        return $linkIds;
    }

    /**
     * Retrieve just added to cart product object
     *
     * @return ProductInterface|null
     */
    private function getLastAddedProduct(): ?ProductInterface
    {
        $product = null;
        $productId = $this->_getLastAddedProductId();
        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
        }
        return $product;
    }

    /**
     * Retrieve Array of Product instances in Cart
     *
     * @return array
     */
    private function getCartProducts(): array
    {
        if ($this->cartProducts === null) {
            $this->cartProducts = [];
            foreach ($this->getQuote()->getAllItems() as $quoteItem) {
                /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
                $product = $quoteItem->getProduct();
                if ($product) {
                    $this->cartProducts[$product->getEntityId()] = $product;
                }
            }
        }
        return $this->cartProducts;
    }
}
