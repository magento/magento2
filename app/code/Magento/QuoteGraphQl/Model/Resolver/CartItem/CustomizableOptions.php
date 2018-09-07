<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\CartItem;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType as DefaultOptionType;
use Magento\Catalog\Model\Product\Option\Type\Select as SelectOptionType;
use Magento\Catalog\Model\Product\Option\Type\Text as TextOptionType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * {@inheritdoc}
 */
class CustomizableOptions implements ResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ValueFactory $valueFactory,
        UserContextInterface $userContext,
        StoreManagerInterface $storeManager
    ) {
        $this->valueFactory = $valueFactory;
        $this->userContext = $userContext;
        $this->storeManager = $storeManager;
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
            $customOptionData = $this->getOptionData($cartItem, (int) $optionId);

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

    /**
     * @param QuoteItem $cartItem
     * @param int $optionId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getOptionData($cartItem, int $optionId): array
    {
        $product = $cartItem->getProduct();
        $option = $product->getOptionById($optionId);

        if (!$option) {
            return [];
        }

        $itemOption = $cartItem->getOptionByCode('option_' . $option->getId());

        /** @var SelectOptionType|TextOptionType|DefaultOptionType $optionTypeGroup */
        $optionTypeGroup = $option->groupFactory($option->getType())
            ->setOption($option)
            ->setConfigurationItem($cartItem)
            ->setConfigurationItemOption($itemOption);

        if (ProductCustomOptionInterface::OPTION_GROUP_FILE == $option->getType()) {
            $downloadParams = $cartItem->getFileDownloadParams();

            if ($downloadParams) {
                $url = $downloadParams->getUrl();
                if ($url) {
                    $optionTypeGroup->setCustomOptionDownloadUrl($url);
                }
                $urlParams = $downloadParams->getUrlParams();
                if ($urlParams) {
                    $optionTypeGroup->setCustomOptionUrlParams($urlParams);
                }
            }
        }

        $selectedOptionValueData = [
            'id' => $itemOption->getId(),
            'label' => $optionTypeGroup->getFormattedOptionValue($itemOption->getValue()),
        ];

        if (ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN == $option->getType()
            || ProductCustomOptionInterface::OPTION_TYPE_RADIO == $option->getType()
            || ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX == $option->getType()
        ) {
            $optionValue = $option->getValueById($itemOption->getValue());
            $priceValueUnits = $this->getPriceValueUnits($optionValue->getPriceType());

            $selectedOptionValueData['price'] = [
                'type' => strtoupper($optionValue->getPriceType()),
                'units' => $priceValueUnits,
                'value' => $optionValue->getPrice(),
            ];

            $selectedOptionValueData = [$selectedOptionValueData];
        }

        if (ProductCustomOptionInterface::OPTION_TYPE_FIELD == $option->getType()
            || ProductCustomOptionInterface::OPTION_TYPE_AREA == $option->getType()
            || ProductCustomOptionInterface::OPTION_GROUP_DATE == $option->getType()
            || ProductCustomOptionInterface::OPTION_TYPE_TIME == $option->getType()
        ) {
            $priceValueUnits = $this->getPriceValueUnits($option->getPriceType());

            $selectedOptionValueData['price'] = [
                'type' => strtoupper($option->getPriceType()),
                'units' => $priceValueUnits,
                'value' => $option->getPrice(),
            ];

            $selectedOptionValueData = [$selectedOptionValueData];
        }

        if (ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE == $option->getType()) {
            $selectedOptionValueData = [];
            $optionIds = explode(',', $itemOption->getValue());

            foreach ($optionIds as $optionId) {
                $optionValue = $option->getValueById($optionId);
                $priceValueUnits = $this->getPriceValueUnits($optionValue->getPriceType());

                $selectedOptionValueData[] = [
                    'id' => $itemOption->getId(),
                    'label' => $optionValue->getTitle(),
                    'price' => [
                        'type' => strtoupper($optionValue->getPriceType()),
                        'units' => $priceValueUnits,
                        'value' => $optionValue->getPrice(),
                    ],
                ];
            }
        }

        return [
            'id' => $option->getId(),
            'label' => $option->getTitle(),
            'type' => $option->getType(),
            'values' => $selectedOptionValueData,
            'sort_order' => $option->getSortOrder(),
        ];
    }

    /**
     * @param string $priceType
     * @return string
     * @throws NoSuchEntityException
     */
    private function getPriceValueUnits(string $priceType): string
    {
        if (ProductPriceOptionsInterface::VALUE_PERCENT == $priceType) {
            return '%';
        }

        return $this->getCurrencySymbol();
    }

    /**
     * Get currency symbol
     * @return string
     * @throws NoSuchEntityException
     */
    private function getCurrencySymbol(): string
    {
        /** @var Store|StoreInterface $store */
        $store = $this->storeManager->getStore();

        return $store->getBaseCurrency()->getCurrencySymbol();
    }
}