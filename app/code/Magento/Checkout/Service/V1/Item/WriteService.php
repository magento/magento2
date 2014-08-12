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

namespace Magento\Checkout\Service\V1\Item;

use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Checkout\Service\V1\Data\Cart\ItemBuilder as ItemBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Checkout\Service\V1\QuoteLoader
     */
    protected $quoteLoader;

    /**
     * @var ItemBuilder
     */
    protected $itemBuilder;

    /**
     * @var \Magento\Catalog\Service\V1\Product\ProductLoader
     */
    protected $productLoader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Checkout\Service\V1\QuoteLoader $quoteLoader
     * @param ItemBuilder $itemBuilder
     * @param \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Checkout\Service\V1\QuoteLoader $quoteLoader,
        ItemBuilder $itemBuilder,
        \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->quoteLoader = $quoteLoader;
        $this->itemBuilder = $itemBuilder;
        $this->productLoader = $productLoader;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addItem($cartId, \Magento\Checkout\Service\V1\Data\Cart\Item $data)
    {
        $qty = $data->getQty();
        if (!is_numeric($qty) || $qty <= 0) {
            throw InputException::invalidFieldValue('qty', $qty);
        }
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteLoader->load($cartId, $storeId);

        $product = $this->productLoader->load($data->getSku());

        try {
            $quote->addProduct($product, $qty);
            $quote->collectTotals()->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not add item to quote');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateItem($cartId, $itemSku, \Magento\Checkout\Service\V1\Data\Cart\Item $data)
    {
        $qty = $data->getQty();
        if (!is_numeric($qty) || $qty <= 0) {
            throw InputException::invalidFieldValue('qty', $qty);
        }
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteLoader->load($cartId, $storeId);
        $product = $this->productLoader->load($itemSku);
        $quoteItem = $quote->getItemByProduct($product);
        if (!$quoteItem) {
            throw new NoSuchEntityException("Cart $cartId doesn't contain product $itemSku");
        }
        $quoteItem->setData('qty', $qty);

        try {
            $quote->collectTotals()->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not update quote item');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeItem($cartId, $itemSku)
    {
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteLoader->load($cartId, $storeId);
        $product = $this->productLoader->load($itemSku);
        $quoteItem = $quote->getItemByProduct($product);
        if (!$quoteItem) {
            throw new NoSuchEntityException("Cart $cartId doesn't contain product $itemSku");
        }
        try {
            $quote->removeItem($quoteItem->getId());
            $quote->collectTotals()->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not remove item from quote');
        }
        return true;
    }
}
