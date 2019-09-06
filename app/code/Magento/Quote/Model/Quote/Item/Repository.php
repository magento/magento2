<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;

/**
 * Repository for quote item.
 */
class Repository implements CartItemRepositoryInterface
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Product repository.
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CartItemInterfaceFactory
     */
    protected $itemDataFactory;

    /**
     * @var CartItemProcessorInterface[]
     */
    protected $cartItemProcessors;

    /**
     * @var CartItemOptionsProcessor
     */
    private $cartItemOptionsProcessor;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemInterfaceFactory $itemDataFactory
     * @param CartItemOptionsProcessor $cartItemOptionsProcessor
     * @param CartItemProcessorInterface[] $cartItemProcessors
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        CartItemInterfaceFactory $itemDataFactory,
        CartItemOptionsProcessor $cartItemOptionsProcessor,
        array $cartItemProcessors = []
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->itemDataFactory = $itemDataFactory;
        $this->cartItemOptionsProcessor = $cartItemOptionsProcessor;
        $this->cartItemProcessors = $cartItemProcessors;
    }

    /**
     * @inheritdoc
     */
    public function getList($cartId)
    {
        $output = [];
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var  \Magento\Quote\Model\Quote\Item  $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $item = $this->cartItemOptionsProcessor->addProductOptions($item->getProductType(), $item);
            $output[] = $this->cartItemOptionsProcessor->applyCustomOptions($item);
        }
        return $output;
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Quote\Api\Data\CartItemInterface $cartItem)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $cartId = $cartItem->getQuoteId();
        if (!$cartId) {
            throw new InputException(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'quoteId'])
            );
        }

        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItems = $quote->getItems();
        $quoteItems[] = $cartItem;
        $quote->setItems($quoteItems);
        $this->quoteRepository->save($quote);
        $quote->collectTotals();
        return $quote->getLastAddedItem();
    }

    /**
     * @inheritdoc
     */
    public function deleteById($cartId, $itemId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(
                __('The %1 Cart doesn\'t contain the %2 item.', $cartId, $itemId)
            );
        }
        try {
            $quote->removeItem($itemId);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The item couldn't be removed from the quote."));
        }

        return true;
    }
}
