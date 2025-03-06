<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Helper\Catalog\Product;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Pricing\Price\TaxPrice;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product\Configuration as ProductConfiguration;
use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Helper for fetching properties by product configuration item
 * @api
 * @since 100.0.2
 */
class Configuration extends AbstractHelper implements ConfigurationInterface
{
    /**
     * Core data
     *
     * @var Data
     */
    protected $pricingHelper;

    /**
     * Catalog product configuration
     *
     * @var ProductConfiguration
     */
    protected $productConfiguration;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Serializer interface instance.
     *
     * @var Json
     */
    private $serializer;

    /**
     * @var TaxPrice
     */
    private $taxHelper;

    /**
     * @param Context $context
     * @param ProductConfiguration $productConfiguration
     * @param Data $pricingHelper
     * @param Escaper $escaper
     * @param Json|null $serializer
     * @param TaxPrice|null $taxHelper
     */
    public function __construct(
        Context              $context,
        ProductConfiguration $productConfiguration,
        Data                 $pricingHelper,
        Escaper              $escaper,
        ?Json                 $serializer = null,
        ?TaxPrice $taxHelper = null
    ) {
        $this->productConfiguration = $productConfiguration;
        $this->pricingHelper = $pricingHelper;
        $this->escaper = $escaper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
        $this->taxHelper = $taxHelper ?? ObjectManager::getInstance()->get(TaxPrice::class);
        parent::__construct($context);
    }

    /**
     * Get selection quantity
     *
     * @param Product $product
     * @param int $selectionId
     * @return float
     */
    public function getSelectionQty(Product $product, $selectionId)
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
     * @param Product $selectionProduct
     * @return float
     */
    public function getSelectionFinalPrice(ItemInterface $item, Product $selectionProduct)
    {
        $selectionProduct->unsetData('final_price');

        $product = $item->getProduct();
        /** @var Price $price */
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

        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption
            ? $this->serializer->unserialize($optionsQuoteItemOption->getValue())
            : [];

        if ($bundleOptionsIds) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $bundleSelectionIds = $this->serializer->unserialize($selectionsQuoteItemOption->getValue());

            if (!empty($bundleSelectionIds)) {
                $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);

                $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
                foreach ($bundleOptions as $bundleOption) {
                    if ($bundleOption->getSelections()) {
                        $option = ['label' => $bundleOption->getTitle(), 'value' => []];

                        $bundleSelections = $bundleOption->getSelections();

                        foreach ($bundleSelections as $bundleSelection) {
                            $option = $this->getOptionPriceHtml($item, $bundleSelection, $option);
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
     * Get bundle options' prices
     *
     * @param ItemInterface $item
     * @param ProductInterface $bundleSelection
     * @param array $option
     * @return array
     * @throws LocalizedException
     */
    private function getOptionPriceHtml(ItemInterface $item, ProductInterface $bundleSelection, array $option): array
    {
        $product = $item->getProduct();
        $qty = $this->getSelectionQty($item->getProduct(), $bundleSelection->getSelectionId()) * 1;
        if ($qty) {
            $selectionPrice = $this->getSelectionFinalPrice($item, $bundleSelection);

            $displayCartPricesBoth = $this->taxHelper->displayCartPricesBoth();
            if ($displayCartPricesBoth) {
                $selectionFinalPrice =
                    $this->taxHelper
                        ->getTaxPrice($product, $selectionPrice, true);
                $selectionFinalPriceExclTax =
                    $this->taxHelper
                        ->getTaxPrice($product, $selectionPrice, false);
            } else {
                $selectionFinalPrice = $this->taxHelper->getTaxPrice($item->getProduct(), $selectionPrice);
            }
            $option['value'][] = $qty . ' x '
                . $this->escaper->escapeHtml($bundleSelection->getName())
                . ' '
                . $this->pricingHelper->currency(
                    $selectionFinalPrice
                )
                . ($displayCartPricesBoth ? ' ' . __('Excl. tax:') . ' '
                    . $this->pricingHelper->currency(
                        $selectionFinalPriceExclTax
                    ) : '');
            $option['has_html'] = true;
        }
        return $option;
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
