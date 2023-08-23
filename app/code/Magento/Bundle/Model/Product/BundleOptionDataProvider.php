<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Data provider for bundled product options
 */
class BundleOptionDataProvider
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'bundle';

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
     * @var Uid
     */
    private $uidEncoder;

    /**
     * @param Data $pricingHelper
     * @param SerializerInterface $serializer
     * @param Configuration $configuration
     * @param Uid $uidEncoder
     */
    public function __construct(
        Data $pricingHelper,
        SerializerInterface $serializer,
        Configuration $configuration,
        Uid $uidEncoder
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->serializer = $serializer;
        $this->configuration = $configuration;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Extract data for a bundled item
     *
     * @param ItemInterface $item
     *
     * @return array
     */
    public function getData(ItemInterface $item): array
    {
        $options = [];
        $product = $item->getProduct();
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption
            ? $this->serializer->unserialize($optionsQuoteItemOption->getValue())
            : [];

        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        if ($bundleOptionsIds) {
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);
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
     * @param Option[] $bundleOptions
     * @param ItemInterface $item
     *
     * @return array
     */
    private function buildBundleOptions(array $bundleOptions, ItemInterface $item): array
    {
        $options = [];
        foreach ($bundleOptions as $bundleOption) {
            if (!$bundleOption->getSelections()) {
                continue;
            }

            $optionDetails = [
                self::OPTION_TYPE,
                $bundleOption->getOptionId()
            ];
            $uidString = implode('/', $optionDetails);

            $options[] = [
                'id' => $bundleOption->getId(),
                'uid' => $this->uidEncoder->encode($uidString),
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
     * @param ItemInterface $item
     *
     * @return array
     */
    private function buildBundleOptionValues(array $selections, ItemInterface $item): array
    {
        $product = $item->getProduct();
        $values = [];

        foreach ($selections as $selection) {
            $qty = (float) $this->configuration->getSelectionQty($product, $selection->getSelectionId());
            if (!$qty) {
                continue;
            }

            $optionValueDetails = [
                self::OPTION_TYPE,
                $selection->getOptionId(),
                $selection->getSelectionId(),
                (int) $selection->getSelectionQty()
            ];
            $uidString = implode('/', $optionValueDetails);

            $selectionPrice = $this->configuration->getSelectionFinalPrice($item, $selection);
            $values[] = [
                'id' => $selection->getSelectionId(),
                'uid' => $this->uidEncoder->encode($uidString),
                'label' => $selection->getName(),
                'quantity' => $qty,
                'price' => $this->pricingHelper->currency($selectionPrice, false, false),
            ];
        }

        return $values;
    }
}
