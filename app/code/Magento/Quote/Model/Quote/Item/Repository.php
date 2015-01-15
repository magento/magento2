<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements \Magento\Quote\Api\CartItemRepositoryInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Product repository.
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartItemDataBuilder
     */
    protected $itemDataBuilder;

    /**
     * Constructs a read service object.
     *
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Api\Data\CartItemDataBuilder $itemDataBuilder
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\CartItemDataBuilder $itemDataBuilder
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->itemDataBuilder = $itemDataBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $output = [];
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var  \Magento\Quote\Model\Quote\Item  $item */
        foreach ($quote->getAllItems() as $item) {
            $output[] = $item;
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Quote\Api\Data\CartItemInterface $cartItem)
    {
        $qty = $cartItem->getQty();
        if (!is_numeric($qty) || $qty <= 0) {
            throw InputException::invalidFieldValue('qty', $qty);
        }
        $cartId = $cartItem->getQuoteId();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $itemId = $cartItem->getItemId();
        try {
            /** update item qty */
            if (isset($itemId)) {
                $cartItem = $quote->getItemById($itemId);
                if (!$cartItem) {
                    throw new NoSuchEntityException("Cart $cartId doesn't contain item  $itemId");
                }
                $product = $this->productRepository->get($cartItem->getSku());
                $cartItem->setData('qty', $qty);
            } else {
                $product = $this->productRepository->get($cartItem->getSku());
                $quote->addProduct($product, $qty);
            }
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            if ($e instanceof NoSuchEntityException) {
                throw $e;
            }
            throw new CouldNotSaveException('Could not save quote');
        }
        return $quote->getItemByProduct($product);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Quote\Api\Data\CartItemInterface $cartItem)
    {
        $cartId = $cartItem->getQuoteId();
        $itemId = $cartItem->getItemId();
        /**
         * Quote.
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException("Cart $cartId doesn't contain item  $itemId");
        }
        try {
            $quote->removeItem($itemId);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not remove item from quote');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($cartId, $itemId)
    {
        $item = $this->itemDataBuilder->setQuoteId($cartId)->setItemId($itemId)->create();
        $this->delete($item);
        return true;
    }
}
