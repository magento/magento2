<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Quote\Item;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Bundle product options model
 */
class Option
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param Json $serializer
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Json $serializer,
        ?PriceCurrencyInterface $priceCurrency = null,
    ) {
        $this->serializer = $serializer;
        $this->priceCurrency = $priceCurrency ?? ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
    }

    /**
     * Get selection options for provided bundle product
     *
     * @param Product $product
     * @return array
     */
    public function getSelectionOptions(Product $product): array
    {
        $options = [];
        $bundleOptionIds = $this->getOptionValueAsArray($product, 'bundle_option_ids');
        if ($bundleOptionIds) {
            /** @var Type $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionIds, $product);
            $selectionIds = $this->getOptionValueAsArray($product, 'bundle_selection_ids');

            if ($selectionIds) {
                $selectionsCollection = $typeInstance->getSelectionsByIds($selectionIds, $product);
                $optionsCollection->appendSelections($selectionsCollection, true);

                foreach ($selectionsCollection as $selection) {
                    $selectionId = $selection->getSelectionId();
                    $options[$selectionId][] = $this->getBundleSelectionAttributes($product, $selection);
                }
            }
        }

        return $options;
    }

    /**
     * Get selection attributes for provided selection
     *
     * @param Product $product
     * @param Product $selection
     * @return array
     */
    private function getBundleSelectionAttributes(Product $product, Product $selection): array
    {
        $selectionId = $selection->getSelectionId();
        /** @var \Magento\Bundle\Model\Option $bundleOption */
        $bundleOption = $selection->getOption();
        /** @var Price $priceModel */
        $priceModel = $product->getPriceModel();
        $price = $priceModel->getSelectionFinalTotalPrice($product, $selection, 0, 1);
        $customOption = $product->getCustomOption('selection_qty_' . $selectionId);
        $qty = (float)($customOption ? $customOption->getValue() : 0);

        return [
            'code' => 'bundle_selection_attributes',
            'value'=> $this->serializer->serialize(
                [
                    'price' => $this->priceCurrency->convert($price, $product->getStore()),
                    'qty' => $qty,
                    'option_label' => $bundleOption->getTitle(),
                    'option_id' => $bundleOption->getId(),
                ]
            )
        ];
    }

    /**
     * Get unserialized value of custom option
     *
     * @param Product $product
     * @param string $code
     * @return array
     */
    private function getOptionValueAsArray(Product $product, string $code): array
    {
        $option = $product->getCustomOption($code);
        return $option && $option->getValue()
            ? $this->serializer->unserialize($option->getValue())
            : [];
    }
}
