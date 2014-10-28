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
namespace Magento\Checkout\Block\Cart;

/**
 * Cart crosssell list
 *
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
     * @var \Magento\CatalogInventory\Model\Stock
     */
    protected $_stock;

    /**
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
    protected $_productLinkFactory;

    /**
     * @var \Magento\Sales\Model\Quote\Item\RelatedProducts
     */
    protected $_itemRelationsList;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\CatalogInventory\Model\Stock $stock
     * @param \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory
     * @param \Magento\Sales\Model\Quote\Item\RelatedProducts $itemRelationsList
     * @param array $data
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\CatalogInventory\Model\Stock $stock,
        \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory,
        \Magento\Sales\Model\Quote\Item\RelatedProducts $itemRelationsList,
        array $data = array()
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_productVisibility = $productVisibility;
        $this->_stock = $stock;
        $this->_productLinkFactory = $productLinkFactory;
        $this->_itemRelationsList = $itemRelationsList;
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
        if (is_null($items)) {
            $items = array();
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
        if (is_null($ids)) {
            $ids = array();
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
     */
    protected function _getLastAddedProductId()
    {
        return $this->_checkoutSession->getLastAddedProductId(true);
    }

    /**
     * Get quote instance
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Get crosssell products collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Link\Product\Collection
     */
    protected function _getCollection()
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Link\Product\Collection $collection */
        $collection = $this->_productLinkFactory->create()->useCrossSellLinks()->getProductCollection()->setStoreId(
            $this->_storeManager->getStore()->getId()
        )->addStoreFilter()->setPageSize(
            $this->_maxItemCount
        )->setVisibility(
            $this->_productVisibility->getVisibleInCatalogIds()
        );
        $this->_addProductAttributesAndPrices($collection);

        $this->_stock->addInStockFilterToCollection($collection);

        return $collection;
    }
}
