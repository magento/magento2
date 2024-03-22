<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\QuoteMutexInterface;
use Magento\Quote\Model\QuoteRepository;

/**
 * Repository for quote item.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Repository implements CartItemRepositoryInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
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
     * @var ?QuoteMutexInterface
     */
    private ?QuoteMutexInterface $quoteMutex;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemInterfaceFactory $itemDataFactory
     * @param CartItemOptionsProcessor $cartItemOptionsProcessor
     * @param CartItemProcessorInterface[] $cartItemProcessors
     * @param QuoteMutexInterface|null $quoteMutex
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        CartItemInterfaceFactory $itemDataFactory,
        CartItemOptionsProcessor $cartItemOptionsProcessor,
        array $cartItemProcessors = [],
        ?QuoteMutexInterface $quoteMutex = null
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->itemDataFactory = $itemDataFactory;
        $this->cartItemOptionsProcessor = $cartItemOptionsProcessor;
        $this->cartItemProcessors = $cartItemProcessors;
        $this->quoteMutex = $quoteMutex ?: ObjectManager::getInstance()->get(QuoteMutexInterface::class);
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
    public function save(CartItemInterface $cartItem)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $cartId = $cartItem->getQuoteId();
        if (!$cartId) {
            throw new InputException(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'quoteId'])
            );
        }

        return $this->quoteMutex->execute(
            [$cartId],
            \Closure::fromCallable([$this, 'saveItem']),
            [$cartItem]
        );
    }

    /**
     * Save cart item.
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function saveItem(CartItemInterface $cartItem)
    {
        $cartId = (int)$cartItem->getQuoteId();
        if ($this->quoteRepository instanceof QuoteRepository) {
            $quote = $this->getNonCachedActiveQuote($cartId);
        } else {
            $quote = $this->quoteRepository->getActive($cartId);
        }
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

    /**
     * Returns quote repository without internal cache.
     *
     * Prevents usage of cached quote that causes incorrect quote items update by concurrent web-api requests.
     *
     * @param int $cartId
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    private function getNonCachedActiveQuote(int $cartId): CartInterface
    {
        $cachedQuote = $this->quoteRepository->getActive($cartId);
        $className = get_class($this->quoteRepository);
        $quote = ObjectManager::getInstance()->create($className)->getActive($cartId);
        foreach ($quote->getItems() as $quoteItem) {
            $cachedQuoteItem = $cachedQuote->getItemById($quoteItem->getId());
            if ($cachedQuoteItem) {
                $quoteItem->setExtensionAttributes($cachedQuoteItem->getExtensionAttributes());
            }
        }

        return $quote;
    }
}
