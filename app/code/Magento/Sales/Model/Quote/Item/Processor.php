<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Quote\ItemFactory;
use Magento\Sales\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\Object;

/**
 * Class Processor
 *  - initializes quote item with store_id and qty data
 *  - updates quote item qty and custom price data
 */
class Processor
{
    /**
     * @var \Magento\Sales\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @param ItemFactory $quoteItemFactory
     * @param StoreManagerInterface $storeManager
     * @param State $appState
     */
    public function __construct(
        ItemFactory $quoteItemFactory,
        StoreManagerInterface $storeManager,
        State $appState
    ) {
        $this->quoteItemFactory = $quoteItemFactory;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
    }

    /**
     * Initialize quote item object
     *
     * @param \Magento\Framework\Object $request
     * @param Product $product
     *
     * @return \Magento\Sales\Model\Quote\Item
     */
    public function init(Product $product, $request)
    {
        $item = $this->quoteItemFactory->create();

        $this->setItemStoreId($item);

        /**
         * We can't modify existing child items
         */
        if ($item->getId() && $product->getParentProductId()) {
            return $item;
        }

        $item->setOptions($product->getCustomOptions());
        $item->setProduct($product);

        if ($request->getResetCount() && !$product->getStickWithinParent() && $item->getId() === $request->getId()) {
            $item->setData('qty', 0);
        }

        return $item;
    }

    /**
     * Set qty and custom price for quote item
     *
     * @param Item $item
     * @param Object $request
     * @param Product $candidate
     * @return void
     */
    public function prepare(Item $item, Object $request, Product $candidate)
    {
        /**
         * We specify qty after we know about parent (for stock)
         */
        $item->addQty($candidate->getCartQty());

        $customPrice = $request->getCustomPrice();
        if (!empty($customPrice)) {
            $item->setCustomPrice($customPrice);
            $item->setOriginalCustomPrice($customPrice);
        }
    }

    /**
     * Set store_id value to quote item
     *
     * @param Item $item
     * @return void
     */
    protected function setItemStoreId(Item $item)
    {
        if ($this->appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            $storeId = $this->storeManager->getStore($this->storeManager->getStore()->getId())
                ->getId();
            $item->setStoreId($storeId);
        } else {
            $item->setStoreId($this->storeManager->getStore()->getId());
        }
    }
}
