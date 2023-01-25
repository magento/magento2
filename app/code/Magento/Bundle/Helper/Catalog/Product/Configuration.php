<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Helper\Catalog\Product;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;

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
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * Catalog product configuration
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfiguration;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelper;

    /**
     * @var \Magento\Tax\Helper\Data|mixed
     */
    private $taxHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfiguration
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param \Magento\Catalog\Helper\Data|null $catalogHelper
     * @param \Magento\Tax\Helper\Data|null $taxHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \Magento\Catalog\Helper\Data $catalogHelper = null,
        \Magento\Tax\Helper\Data $taxHelper = null
    ) {
        $this->productConfiguration = $productConfiguration;
        $this->pricingHelper = $pricingHelper;
        $this->escaper = $escaper;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->catalogHelper = $catalogHelper ?? ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Data::class);
        $this->taxHelper = $taxHelper ?? ObjectManager::getInstance()->get(\Magento\Tax\Helper\Data::class);
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
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function getBundleOptions(ItemInterface $item)
    {
        $options = [];
        $product = $item->getProduct();

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
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
                            $qty = $this->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                            if ($qty) {
                                $selectionPrice = $this->getSelectionFinalPrice($item, $bundleSelection);
                                $selectionFinalPrice = $this->catalogHelper->getTaxPrice($item, $selectionPrice);

                                $displayBothPrices = $this->taxHelper->displayBothPrices();
                                if ($displayBothPrices) {
                                    $selectionFinalPrice = $this->catalogHelper->getTaxPrice($item, $selectionPrice, true);
                                    $selectionFinalPriceExclTax = $this->catalogHelper->getTaxPrice($item, $selectionPrice, false);
                                }
                                $option['value'][] = $qty . ' x '
                                    . $this->escaper->escapeHtml($bundleSelection->getName())
                                    . ' '
                                    . $this->pricingHelper->currency(
                                        $selectionFinalPrice
                                    )
                                    . ' '
                                    . ($displayBothPrices ? __('Excl. tax:') . ' '
                                    . $this->pricingHelper->currency(
                                        $selectionFinalPriceExclTax
                                    ) : '');
                                $option['has_html'] = true;
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
    //phpcs:enable Generic.Metrics.NestingLevel

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
