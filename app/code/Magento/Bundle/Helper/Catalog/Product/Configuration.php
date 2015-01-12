<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Helper\Catalog\Product;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper for fetching properties by product configuration item
 */
class Configuration extends AbstractHelper implements ConfigurationInterface
{
    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $coreData;

    /**
     * Catalog product configuration
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfiguration;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfiguration
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->productConfiguration = $productConfiguration;
        $this->coreData = $coreData;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Get selection quantity
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $selectionId
     * @return float
     */
    public function getSelectionQty(\Magento\Catalog\Model\Product $product, $selectionId)
    {
        $selectionQty = $product->getCustomOption('selection_qty_' . $selectionId);
        if ($selectionQty) {
            return $selectionQty->getValue();
        }
        return 0;
    }

    /**
     * Obtain final price of selection in a bundle product
     *
     * @param ItemInterface $item
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @return float
     */
    public function getSelectionFinalPrice(ItemInterface $item, \Magento\Catalog\Model\Product $selectionProduct)
    {
        $selectionProduct->unsetData('final_price');

        $product = $item->getProduct();
        /** @var \Magento\Bundle\Model\Product\Price $price */
        $price = $product->getPriceModel();

        return $price->getSelectionFinalTotalPrice(
            $product,
            $selectionProduct,
            $item->getQty(),
            $this->getSelectionQty($product, $selectionProduct->getSelectionId()),
            false,
            true
        );
    }

    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param ItemInterface $item
     * @return array
     */
    public function getBundleOptions(ItemInterface $item)
    {
        $options = [];
        $product = $item->getProduct();

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption ? unserialize($optionsQuoteItemOption->getValue()) : [];
        if ($bundleOptionsIds) {
            /** @var \Magento\Bundle\Model\Resource\Option\Collection $optionsCollection */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $bundleSelectionIds = unserialize($selectionsQuoteItemOption->getValue());

            if (!empty($bundleSelectionIds)) {
                $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);

                $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
                foreach ($bundleOptions as $bundleOption) {
                    if ($bundleOption->getSelections()) {
                        $option = ['label' => $bundleOption->getTitle(), 'value' => []];

                        $bundleSelections = $bundleOption->getSelections();

                        foreach ($bundleSelections as $bundleSelection) {
                            $qty = $this->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                            if ($qty) {
                                $option['value'][] = $qty . ' x '
                                    . $this->escaper->escapeHtml($bundleSelection->getName())
                                    . ' '
                                    . $this->coreData->currency($this->getSelectionFinalPrice($item, $bundleSelection));
                            }
                        }

                        if ($option['value']) {
                            $options[] = $option;
                        }
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Retrieves product options list
     *
     * @param ItemInterface $item
     * @return array
     */
    public function getOptions(ItemInterface $item)
    {
        return array_merge(
            $this->getBundleOptions($item),
            $this->productConfiguration->getCustomOptions($item)
        );
    }
}
