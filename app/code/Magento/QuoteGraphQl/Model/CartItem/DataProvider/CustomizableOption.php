<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Custom Option Data provider
 */
class CustomizableOption
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'custom-option';

    /**
     * @var CustomizableOptionValueInterface
     */
    private $customizableOptionValue;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param CustomizableOptionValueInterface $customOptionValueDataProvider
     * @param Uid|null $uidEncoder
     */
    public function __construct(
        CustomizableOptionValueInterface $customOptionValueDataProvider,
        ?Uid $uidEncoder = null
    ) {
        $this->customizableOptionValue = $customOptionValueDataProvider;
        $this->uidEncoder = $uidEncoder ?: ObjectManager::getInstance()
            ->get(Uid::class);
    }

    /**
     * Retrieve custom option data
     *
     * @param QuoteItem $cartItem
     * @param int $optionId
     * @return array
     * @throws LocalizedException
     */
    public function getData(QuoteItem $cartItem, int $optionId): array
    {
        $product = $cartItem->getProduct();
        $option = $product->getOptionById($optionId);

        if (!$option) {
            return [];
        }

        $selectedOption = $cartItem->getOptionByCode('option_' . $option->getId());

        $selectedOptionValueData = $this->customizableOptionValue->getData(
            $cartItem,
            $option,
            $selectedOption
        );

        return [
            'id' => $option->getId(),
            'customizable_option_uid' => $this->uidEncoder->encode((string) self::OPTION_TYPE . '/' . $option->getId()),
            'label' => $option->getTitle(),
            'type' => $option->getType(),
            'values' => $selectedOptionValueData,
            'sort_order' => $option->getSortOrder(),
            'is_required' => $option->getIsRequire(),
        ];
    }
}
