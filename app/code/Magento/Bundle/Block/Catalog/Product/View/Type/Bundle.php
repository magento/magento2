<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\PriceFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * Catalog bundle product info block
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle extends AbstractView
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct;

    /**
     * @var PriceFactory
     */
    protected $productPriceFactory;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var array
     */
    private $selectedOptions = [];

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor
     */
    private $catalogRuleProcessor;

    /**
     * @var array
     */
    private $optionsPosition = [];

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param PriceFactory $productPrice
     * @param EncoderInterface $jsonEncoder
     * @param FormatInterface $localeFormat
     * @param array $data
     * @param CollectionProcessor|null $catalogRuleProcessor
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        \Magento\Catalog\Helper\Product $catalogProduct,
        PriceFactory $productPrice,
        EncoderInterface $jsonEncoder,
        FormatInterface $localeFormat,
        array $data = [],
        ?CollectionProcessor $catalogRuleProcessor = null
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->productPriceFactory = $productPrice;
        $this->jsonEncoder = $jsonEncoder;
        $this->localeFormat = $localeFormat;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
        $this->catalogRuleProcessor = $catalogRuleProcessor ?? ObjectManager::getInstance()
                ->get(CollectionProcessor::class);
    }

    /**
     * Returns the bundle product options
     *
     * Will return cached options data if the product options are already initialized
     * In a case when $stripSelection parameter is true will reload stored bundle selections collection from DB
     *
     * @param bool $stripSelection
     * @return array
     */
    public function getOptions($stripSelection = false)
    {
        if (!$this->options) {
            $product = $this->getProduct();
            /** @var Type $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $typeInstance->getOptionsCollection($product);

            $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($product),
                $product
            );
            $this->catalogRuleProcessor->addPriceData($selectionCollection);
            $selectionCollection->addTierPriceData();

            $this->options = $optionCollection->appendSelections(
                $selectionCollection,
                $stripSelection,
                $this->catalogProduct->getSkipSaleableCheck()
            );
        }

        return $this->options;
    }

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        $this->getOptions();
        return !(empty($this->options) || !$this->getProduct()->isSalable());
    }

    /**
     * Returns JSON encoded config to be used in JS scripts
     *
     * @return string
     */
    public function getJsonConfig()
    {
        /** @var Option[] $optionsArray */
        $optionsArray = $this->getOptions();
        $options = [];
        $currentProduct = $this->getProduct();

        $defaultValues = [];
        $preConfiguredFlag = $currentProduct->hasPreconfiguredValues();
        /** @var DataObject|null $preConfiguredValues */
        $preConfiguredValues = $preConfiguredFlag ? $currentProduct->getPreconfiguredValues() : null;

        $position = 0;
        foreach ($optionsArray as $optionItem) {
            /* @var $optionItem Option */
            if (!$optionItem->getSelections()) {
                continue;
            }
            $optionId = $optionItem->getId();
            $options[$optionId] = $this->getOptionItemData($optionItem, $currentProduct, $position);
            $this->optionsPosition[$position] = $optionId;

            // Add attribute default value (if set)
            if ($preConfiguredFlag) {
                $configValue = $preConfiguredValues->getData('bundle_option/' . $optionId);
                if ($configValue) {
                    $defaultValues[$optionId] = $configValue;
                }
                $options = $this->processOptions($optionId, $options, $preConfiguredValues);
            }
            $position++;
        }
        $config = $this->getConfigData($currentProduct, $options);

        $configObj = new DataObject(
            [
                'config' => $config,
            ]
        );

        //pass the return array encapsulated in an object for the other modules to be able to alter it eg: weee
        $this->_eventManager->dispatch('catalog_product_option_price_configuration_after', ['configObj' => $configObj]);
        $config=$configObj->getConfig();

        if ($preConfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Get html for option
     *
     * @param Option $option
     * @return string
     */
    public function getOptionHtml(Option $option)
    {
        $optionBlock = $this->getChildBlock($option->getType());
        if (!$optionBlock) {
            return __('There is no defined renderer for "%1" option type.', $this->escapeHtml($option->getType()));
        }
        return $optionBlock->setOption($option)->toHtml();
    }

    /**
     * Get formed data from option selection item.
     *
     * @param Product $product
     * @param Product $selection
     *
     * @return array
     */
    private function getSelectionItemData(Product $product, Product $selection)
    {
        $qty = ($selection->getSelectionQty() * 1) ?: '1';

        $optionPriceAmount = $product->getPriceInfo()
            ->getPrice(\Magento\Bundle\Pricing\Price\BundleOptionPrice::PRICE_CODE)
            ->getOptionSelectionAmount($selection);
        $finalPrice = $optionPriceAmount->getValue();
        $basePrice = $optionPriceAmount->getBaseAmount();

        $oldPrice = $product->getPriceInfo()
            ->getPrice(\Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::PRICE_CODE)
            ->getOptionSelectionAmount($selection)
            ->getValue();

        return [
            'qty' => $qty,
            'customQty' => $selection->getSelectionCanChangeQty(),
            'optionId' => $selection->getId(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $oldPrice,
                ],
                'basePrice' => [
                    'amount' => $basePrice,
                ],
                'finalPrice' => [
                    'amount' => $finalPrice,
                ],
            ],
            'priceType' => $selection->getSelectionPriceType(),
            'tierPrice' => $this->getTierPrices($product, $selection),
            'name' => $selection->getName(),
            'canApplyMsrp' => false,
        ];
    }

    /**
     * Get tier prices from option selection item
     *
     * @param Product $product
     * @param Product $selection
     * @return array
     */
    private function getTierPrices(Product $product, Product $selection)
    {
        // recalculate currency
        $tierPrices = $selection->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE)
            ->getTierPriceList();

        foreach ($tierPrices as &$tierPriceInfo) {
            /** @var \Magento\Framework\Pricing\Amount\Base $price */
            $price = $tierPriceInfo['price'];

            $priceBaseAmount = $price->getBaseAmount();
            $priceValue = $price->getValue();

            $bundleProductPrice = $this->productPriceFactory->create();
            $priceBaseAmount = $bundleProductPrice->getLowestPrice($product, $priceBaseAmount);
            $priceValue = $bundleProductPrice->getLowestPrice($product, $priceValue);

            $tierPriceInfo['prices'] = [
                'oldPrice' => [
                    'amount' => $priceBaseAmount
                ],
                'basePrice' => [
                    'amount' => $priceBaseAmount
                ],
                'finalPrice' => [
                    'amount' => $priceValue
                ]
            ];
        }
        return $tierPrices;
    }

    /**
     * Get formed data from selections of option
     *
     * @param Option $option
     * @param Product $product
     * @return array
     */
    private function getSelections(Option $option, Product $product)
    {
        $selections = [];
        $selectionCount = count($option->getSelections());
        foreach ($option->getSelections() as $selectionItem) {
            /* @var $selectionItem Product */
            $selectionId = $selectionItem->getSelectionId();
            $selections[$selectionId] = $this->getSelectionItemData($product, $selectionItem);

            if (($selectionItem->getIsDefault() || $selectionCount == 1 && $option->getRequired())
                && $selectionItem->isSalable()
            ) {
                $this->selectedOptions[$option->getId()][] = $selectionId;
            }
        }
        return $selections;
    }

    /**
     * Get formed data from option
     *
     * @param Option $option
     * @param Product $product
     * @param int $position
     * @return array
     */
    private function getOptionItemData(Option $option, Product $product, $position)
    {
        return [
            'selections' => $this->getSelections($option, $product),
            'title' => $option->getTitle(),
            'isMulti' => in_array($option->getType(), ['multi', 'checkbox']),
            'position' => $position
        ];
    }

    /**
     * Get formed config data from calculated options data
     *
     * @param Product $product
     * @param array $options
     * @return array
     */
    private function getConfigData(Product $product, array $options)
    {
        $isFixedPrice = $this->getProduct()->getPriceType() == Price::PRICE_TYPE_FIXED;

        $productAmount = $product
            ->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getPriceWithoutOption();

        $baseProductAmount = $product
            ->getPriceInfo()
            ->getPrice(RegularPrice::PRICE_CODE)
            ->getAmount();

        $config = [
            'options' => $options,
            'selected' => $this->selectedOptions,
            'positions' => $this->optionsPosition,
            'bundleId' => $product->getId(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $isFixedPrice ? $baseProductAmount->getValue() : 0
                ],
                'basePrice' => [
                    'amount' => $isFixedPrice ? $productAmount->getBaseAmount() : 0
                ],
                'finalPrice' => [
                    'amount' => $isFixedPrice ? $productAmount->getValue() : 0
                ]
            ],
            'priceType' => $product->getPriceType(),
            'isFixedPrice' => $isFixedPrice,
        ];
        return $config;
    }

    /**
     * Set preconfigured quantities and selections to options.
     *
     * @param string $optionId
     * @param array $options
     * @param DataObject $preConfiguredValues
     * @return array
     */
    private function processOptions(string $optionId, array $options, DataObject $preConfiguredValues)
    {
        $preConfiguredQtys = $preConfiguredValues->getData("bundle_option_qty/{$optionId}") ?? [];
        $selections = $options[$optionId]['selections'];
        array_walk(
            $selections,
            function (&$selection, $selectionId) use ($preConfiguredQtys) {
                if (is_array($preConfiguredQtys) && isset($preConfiguredQtys[$selectionId])) {
                    $selection['qty'] = $preConfiguredQtys[$selectionId];
                } else {
                    if ((int)$preConfiguredQtys > 0) {
                        $selection['qty'] = $preConfiguredQtys;
                    }
                }
            }
        );
        $options[$optionId]['selections'] = $selections;

        return $options;
    }
}
