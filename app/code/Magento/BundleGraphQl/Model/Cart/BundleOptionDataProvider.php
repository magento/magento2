<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Cart;

use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Data provider for bundled product options
 */
class BundleOptionDataProvider
{
    /**
     * @var Data
     */
    private $pricingHelper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Data $pricingHelper
     * @param SerializerInterface $serializer
     * @param Configuration $configuration
     */
    public function __construct(
        Data $pricingHelper,
        SerializerInterface $serializer,
        Configuration $configuration
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->serializer = $serializer;
        $this->configuration = $configuration;
    }

    /**
     * Extract data for a bundled cart item
     *
     * @param Item $item
     * @return array
     */
    public function getData(Item $item): array
    {
        $options = [];
        $product = $item->getProduct();

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption
            ? $this->serializer->unserialize($optionsQuoteItemOption->getValue())
            : [];

        if ($bundleOptionsIds) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $bundleSelectionIds = $this->serializer->unserialize($selectionsQuoteItemOption->getValue());

            if (!empty($bundleSelectionIds)) {
                $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);
                $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);

                $options = $this->buildBundleOptions($bundleOptions, $item);
            }
        }

        return $options;
    }

    /**
     * Build bundle product options based on current selection
     *
     * @param \Magento\Bundle\Model\Option[] $bundleOptions
     * @param Item $item
     * @return array
     */
    private function buildBundleOptions(array $bundleOptions, Item $item): array
    {
        $options = [];
        foreach ($bundleOptions as $bundleOption) {
            if (!$bundleOption->getSelections()) {
                continue;
            }

            $options[] = [
                'id' => $bundleOption->getId(),
                'label' => $bundleOption->getTitle(),
                'type' => $bundleOption->getType(),
                'values' => $this->buildBundleOptionValues($bundleOption->getSelections(), $item),
            ];
        }

        return $options;
    }

    /**
     * Build bundle product option values based on current selection
     *
     * @param Product[] $selections
     * @param Item $item
     * @return array
     */
    private function buildBundleOptionValues(array $selections, Item $item): array
    {
        $values = [];

        $product = $item->getProduct();
        foreach ($selections as $selection) {
            $qty = (float) $this->configuration->getSelectionQty($product, $selection->getSelectionId());
            if (!$qty) {
                continue;
            }

            $selectionPrice = $this->configuration->getSelectionFinalPrice($item, $selection);

            $values[] = [
                'id' => $selection->getSelectionId(),
                'label' => $selection->getName(),
                'quantity' => $qty,
                'price' => $this->pricingHelper->currency($selectionPrice, false, false),
            ];
        }

        return $values;
    }
}
