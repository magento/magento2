<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\BuyRequest;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Stdlib\ArrayManagerFactory;

/**
 * Provides QTY buy request data for adding products to cart
 */
class QuantityDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ArrayManagerFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly ArrayManagerFactory $arrayManagerFactory;

    /**
     * @param ArrayManager $arrayManager @deprecated @see $arrayManagerFactory
     * @param ArrayManagerFactory|null $arrayManagerFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ArrayManager $arrayManager,
        ?ArrayManagerFactory $arrayManagerFactory = null,
    ) {
        $this->arrayManagerFactory = $arrayManagerFactory
            ?? ObjectManager::getInstance()->get(ArrayManagerFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $quantity = $this->arrayManagerFactory->create()->get('data/quantity', $cartItemData);
        if (!isset($quantity)) {
            throw new GraphQlInputException(__('Missing key "quantity" in cart item data'));
        }

        $quantity = (float) $quantity;

        if ($quantity <= 0) {
            throw new GraphQlInputException(
                __('Please enter a number greater than 0 in this field.')
            );
        }

        return ['qty' => $quantity];
    }
}
