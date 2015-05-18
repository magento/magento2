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
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory
     */
    protected $itemDataFactory;

    /**
     * Constructs a read service object.
     *
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Api\Data\CartItemInterfaceFactory $itemDataFactory
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $itemDataFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->itemDataFactory = $itemDataFactory;
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
            if (!$item->isDeleted() && !$item->getParentItemId()) {
                $output[] = $item;
            }
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
                    throw new NoSuchEntityException(
                        __('Cart %1 doesn\'t contain item  %2', $cartId, $itemId)
                    );
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
            throw new CouldNotSaveException(__('Could not save quote'));
        }
        return $quote->getItemByProduct($product);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($cartId, $itemId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(
                __('Cart %1 doesn\'t contain item  %2', $cartId, $itemId)
            );
        }
        try {
            $quote->removeItem($itemId);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not remove item from quote'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getListForCustomer($customerId)
    {
        $cart = $this->quoteRepository->getActiveForCustomer($customerId);
        return $this->getList($cart->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function saveForCustomer($customerId, \Magento\Quote\Api\Data\CartItemInterface $cartItem)
    {
        $cart = $this->quoteRepository->getActiveForCustomer($customerId);
        $cartItem->setQuoteId($cart->getId());
        return $this->save($cartItem);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIdForCustomer($customerId, $itemId)
    {
        $cart = $this->quoteRepository->getActiveForCustomer($customerId);
        return $this->deleteById($cart->getId(), $itemId);
    }
}
