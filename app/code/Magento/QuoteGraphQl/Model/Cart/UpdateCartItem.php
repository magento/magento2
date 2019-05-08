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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote;

/**
 * Update cart item
 *
 */
class UpdateCartItem
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Update cart item
     *
     * @param Quote $cart
     * @param int $cartItemId
     * @param float $qty
     * @param null $customizableOptionsData
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(Quote $cart, int $cartItemId, float $qty, array $customizableOptionsData): void
    {
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
