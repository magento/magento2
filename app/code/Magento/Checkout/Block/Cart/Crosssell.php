<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\CatalogInventory\Helper\Stock as StockHelper;

/**
 * Cart crosssell list
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Crosssell extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 4;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var StockHelper
     */
    protected $stockHelper;

    /**
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
    protected $_productLinkFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\RelatedProducts
     */
    protected $_itemRelationsList;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory
     * @param \Magento\Quote\Model\Quote\Item\RelatedProducts $itemRelationsList
     * @param StockHelper $stockHelper
     * @param array $data
     *
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory,
        \Magento\Quote\Model\Quote\Item\RelatedProducts $itemRelationsList,
        StockHelper $stockHelper,
        array $data = []
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
                $lastAdded = (int)$this->_getLastAddedProductId();
                if ($lastAdded) {
                    $collection = $this->_getCollection()->addProductFilter($lastAdded);
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
                        $this->_getCartProductIds(),
                        $this->_itemRelationsList->getRelatedProductIds($this->getQuote()->getAllItems())
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
            foreach ($this->getQuote()->getAllItems() as $item) {
                $product = $item->getProduct();
                if ($product) {
                    $ids[] = $product->getId();
                }
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
}
