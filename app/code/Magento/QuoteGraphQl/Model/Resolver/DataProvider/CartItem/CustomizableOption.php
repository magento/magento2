<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType as DefaultOptionType;
use Magento\Catalog\Model\Product\Option\Type\Select as SelectOptionType;
use Magento\Catalog\Model\Product\Option\Type\Text as TextOptionType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Custom Option Data provider
 */
class CustomizableOption
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve custom option data
     *
     * @param QuoteItem $cartItem
     * @param int $optionId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getData(QuoteItem $cartItem, int $optionId): array
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
     * Retrieve price value unit
     *
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
     *
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
