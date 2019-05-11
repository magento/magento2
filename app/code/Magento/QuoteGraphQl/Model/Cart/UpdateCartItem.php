<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Update cart item
 *
 */
class UpdateCartItem
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        CartItemRepositoryInterface $cartItemRepository,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->cartItemRepository = $cartItemRepository;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Update cart item
     *
     * @param Quote $cart
     * @param int $cartItemId
     * @param float $qty
     * @param array $customizableOptionsData
     * @return void
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     */
    public function execute(Quote $cart, int $cartItemId, float $qty, array $customizableOptionsData): void
    {
        if (count($customizableOptionsData) === 0) { // Update only item's qty
            $this->updateItemQty($cartItemId, $cart, $qty);

            return;
        }

        $customizableOptions = [];
        foreach ($customizableOptionsData as $customizableOption) {
            $customizableOptions[$customizableOption['id']] = $customizableOption['value_string'];
        }

        try {
            $result = $cart->updateItem(
                $cartItemId,
                $this->createBuyRequest($qty, $customizableOptions)
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(
                __(
                    'Could not update cart item: %message',
                    ['message' => $e->getMessage()]
                )
            );
        }

        if (is_string($result)) {
            throw new GraphQlInputException(__(
                'Could not update cart item: %message',
                ['message' => $result]
            ));
        }

        if ($result->getHasError()) {
            throw new GraphQlInputException(__(
                'Could not update cart item: %message',
                ['message' => $result->getMessage(true)]
            ));
        }

        $this->quoteRepository->save($cart);
    }

    /**
     * Updates item qty for the specified cart
     *
     * @param int $itemId
     * @param Quote $cart
     * @param float $qty
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     * @throws GraphQlNoSuchEntityException
     */
    private function updateItemQty(int $itemId, Quote $cart, float $qty)
    {
        $cartItem = $cart->getItemById($itemId);
        if ($cartItem === false) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find cart item with id: %1.', $itemId)
            );
        }
        $cartItem->setQty($qty);
        $this->validateCartItem($cartItem);
        $this->cartItemRepository->save($cartItem);
    }

    /**
     * Validate cart item
     *
     * @param Item $cartItem
     * @return void
     * @throws GraphQlInputException
     */
    private function validateCartItem(Item $cartItem): void
    {
        if ($cartItem->getHasError()) {
            $errors = [];
            foreach ($cartItem->getMessage(false) as $message) {
                $errors[] = $message;
            }
            if (!empty($errors)) {
                throw new GraphQlInputException(
                    __(
                        'Could not update the product with SKU %sku: %message',
                        ['sku' => $cartItem->getSku(), 'message' => __(implode("\n", $errors))]
                    )
                );
            }
        }
    }

    /**
     * Format GraphQl input data to a shape that buy request has
     *
     * @param float $qty
     * @param array $customOptions
     * @return DataObject
     */
    private function createBuyRequest(float $qty, array $customOptions): DataObject
    {
        return $this->dataObjectFactory->create([
            'data' => [
                'qty' => $qty,
                'options' => $customOptions,
            ],
        ]);
    }
}
