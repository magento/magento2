<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\CartItem;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem\CustomizableOption as CustomizableOptionDataProvider;

/**
 * {@inheritdoc}
 */
class CustomizableOptions implements ResolverInterface
{
    /**
     * @var CustomizableOptionDataProvider
     */
    private $customOptionDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param ValueFactory $valueFactory
     * @param UserContextInterface $userContext
     * @param CustomizableOptionDataProvider $customOptionDataProvider
     */
    public function __construct(
        ValueFactory $valueFactory,
        UserContextInterface $userContext,
        CustomizableOptionDataProvider $customOptionDataProvider
    ) {
        $this->valueFactory = $valueFactory;
        $this->userContext = $userContext;
        $this->customOptionDataProvider = $customOptionDataProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        if (!isset($value['model'])) {
            return $this->valueFactory->create(function () {
                return [];
            });
        }

        /** @var QuoteItem $cartItem */
        $cartItem = $value['model'];
        $optionIds = $cartItem->getOptionByCode('option_ids');

        if (!$optionIds) {
            return $this->valueFactory->create(function () {
                return [];
            });
        }

        $customOptions = [];
        $customOptionIds = explode(',', $optionIds->getValue());

        foreach ($customOptionIds as $optionId) {
            $customOptionData = $this->customOptionDataProvider->getData($cartItem, (int) $optionId);

            if (0 === count($customOptionData)) {
                continue;
            }

            $customOptions[] = $customOptionData;
        }

        $result = function () use ($customOptions) {
            return $customOptions;
        };

        return $this->valueFactory->create($result);
    }
}
