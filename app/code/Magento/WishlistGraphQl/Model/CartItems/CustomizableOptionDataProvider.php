<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\WishlistGraphQl\Model\CartItems;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Wishlist\Model\Item;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Data provider for custom options for cart item request
 */
class CustomizableOptionDataProvider implements CartItemsRequestDataProviderInterface
{
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $productCustomOptionRepository;

    /** 
     * @var Uid 
     */
    private $uidEncoder;

    /**
     * @param ProductCustomOptionRepositoryInterface $productCustomOptionRepository
     * @param Uid $uidEncoder
     */
    public function __construct(
        ProductCustomOptionRepositoryInterface $productCustomOptionRepository,
        Uid $uidEncoder
    ) {
        $this->productCustomOptionRepository = $productCustomOptionRepository;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * @inheritdoc
     */
    public function execute(Item $wishlistItem, ?string $sku): array
    {
        $buyRequest = $wishlistItem->getBuyRequest();
        $options = isset($buyRequest['options'])?$buyRequest['options']:[];
        $customOptions = $this->productCustomOptionRepository->getList($sku);
        $selectedOptions = [];
        $enteredOptions = [];
        foreach ($customOptions as $customOption) {
            $optionId = $customOption->getOptionId();

            if (isset($options[$optionId])) {
                $optionType = $customOption->getType();
                if ($optionType === 'field' || $optionType === 'area' || $optionType === 'date') {
                    $enteredOptions[] = [
                        'uid' => $this->uidEncoder->encode("custom-option/$optionId"),
                        'value' => $options[$optionId],
                    ];
                } elseif ($optionType === 'drop_down') {
                    $optionSelectValues = $customOption->getValues();
                    $selectedOptions[] = $this->encodeSelectedOption(
                        (int) $customOption->getOptionId(),
                        (int) $options[$optionId]
                    );

                } elseif ($optionType === 'multiple') {
                    foreach ($options[$optionId] as $multipleOption) {
                        $selectedOptions[] = $this->encodeSelectedOption(
                            (int) $customOption->getOptionId(),
                            (int) $multipleOption
                        );
                    }
                }
            }
        }

        $cartItems['selected_options'] = $selectedOptions;
        $cartItems['entered_options'] = $enteredOptions;
        return $cartItems;
    }

    /**
     * Returns uid of the selected custom option
     *
     * @param int $optionId
     * @param int $optionValueId
     *
     * @return string
     */
    private function encodeSelectedOption(int $optionId, int $optionValueId): string
    {
        return $this->uidEncoder->encode("custom-option/$optionId/$optionValueId");
    }
}
