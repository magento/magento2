<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type;

/**
 * Catalog bundle product info block
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Bundle extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var mixed
     */
    protected $_options = null;

    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_item';

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct = null;

    /**
     * @var \Magento\Bundle\Model\Product\PriceFactory
     */
    protected $_productPrice;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $coreData;

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Bundle\Model\Product\PriceFactory $productPrice
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Locale\FormatInterface $localeFormat
     * @param array $data
     * @param array $priceBlockTypes
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Bundle\Model\Product\PriceFactory $productPrice,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\Locale\FormatInterface $localeFormat,
        array $data = array(),
        array $priceBlockTypes = array()
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->_productPrice = $productPrice;
        $this->coreData = $coreData;
        $this->jsonEncoder = $jsonEncoder;
        $this->_localeFormat = $localeFormat;
        parent::__construct(
            $context,
            $arrayUtils,
            $data,
            $priceBlockTypes
        );
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        if (!$this->_options) {
            $product = $this->getProduct();
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $typeInstance->getOptionsCollection($product);

            $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($product),
                $product
            );

            $this->_options = $optionCollection->appendSelections(
                $selectionCollection,
                false,
                $this->_catalogProduct->getSkipSaleableCheck()
            );
        }

        return $this->_options;
    }

    /**
     * @return bool
     */
    public function hasOptions()
    {
        $this->getOptions();
        if (empty($this->_options) || !$this->getProduct()->isSalable()) {
            return false;
        }
        return true;
    }

    /**
     * Returns JSON encoded config to be used in JS scripts
     *
     * @return string
     */
    public function getJsonConfig()
    {
        /** @var \Magento\Bundle\Model\Option[] $optionsArray */
        $optionsArray = $this->getOptions();
        $options = array();
        $selected = array();
        $currentProduct = $this->getProduct();
        /* @var $bundlePriceModel \Magento\Bundle\Model\Product\Price */
        $bundlePriceModel = $this->_productPrice->create();

        if ($preConfiguredFlag = $currentProduct->hasPreconfiguredValues()) {
            $preConfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues = array();
        }


        $position = 0;
        foreach ($optionsArray as $_option) {
            /* @var $_option \Magento\Bundle\Model\Option */
            if (!$_option->getSelections()) {
                continue;
            }

            $optionId = $_option->getId();
            $option = array(
                'selections' => array(),
                'title' => $_option->getTitle(),
                'isMulti' => in_array($_option->getType(), array('multi', 'checkbox')),
                'position' => $position++
            );

            $selectionCount = count($_option->getSelections());

            foreach ($_option->getSelections() as $_selection) {
                /* @var $_selection \Magento\Catalog\Model\Product */
                $selectionId = $_selection->getSelectionId();
                $_qty = !($_selection->getSelectionQty() * 1) ? '1' : $_selection->getSelectionQty() * 1;
                // recalculate currency
                $tierPrices = $_selection->getTierPrice();

                foreach ($tierPrices as &$tierPriceInfo) {
                    $tierPriceAmount = $_selection->getPriceInfo()->getPrice('regular_price')
                        ->getCustomAmount($tierPriceInfo['price']);
                    $tierPriceInfo['price'] = $this->coreData->currency($tierPriceInfo['price'], false, false);
                    $tierPriceInfo['inclTaxPrice'] = $this->coreData->currency(
                        $tierPriceAmount->getValue(),
                        false,
                        false
                    );
                    $tierPriceInfo['exclTaxPrice'] = $this->coreData->currency(
                        $tierPriceAmount->getBaseAmount(),
                        false,
                        false
                    );
                }
                // unset($tierPriceInfo);
                // break the reference with the last element

                $canApplyMAP = false;

                $bundleOptionPriceAmount = $currentProduct->getPriceInfo()->getPrice('bundle_option')
                    ->getOptionSelectionAmount($_selection);
                $_priceInclTax = $bundleOptionPriceAmount->getValue();
                $_priceExclTax = $bundleOptionPriceAmount->getBaseAmount();

                //if ($currentProduct->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                //    $_priceInclTax = $this->_taxData->getPrice($currentProduct, $itemPrice, true);
                //    $_priceExclTax = $this->_taxData->getPrice($currentProduct, $itemPrice);
                //}
                //$itemPrice = $bundlePriceModel->getSelectionFinalTotalPrice(
                //    $currentProduct,
                //    $_selection,
                //    $currentProduct->getQty(),
                //    $_selection->getQty(),
                //   false,
                //    false
                //);

                $selection = array(
                    'qty' => $_qty,
                    'customQty' => $_selection->getSelectionCanChangeQty(),
                    'inclTaxPrice' => $this->coreData->currency(
                        $_priceInclTax,
                        false,
                        false
                    ),
                    'exclTaxPrice' => $this->coreData->currency(
                        $_priceExclTax,
                        false,
                        false
                    ),
                    'priceValue' => $this->coreData->currency($_selection->getSelectionPriceValue(), false, false),
                    'priceType' => $_selection->getSelectionPriceType(),
                    'tierPrice' => $tierPrices,
                    'name' => $_selection->getName(),
                    'plusDisposition' => 0,
                    'minusDisposition' => 0,
                    'canApplyMAP' => $canApplyMAP
                );

                $selection['price'] = $this->_taxData->displayPriceIncludingTax()
                    ? $selection['inclTaxPrice']
                    : $selection['exclTaxPrice'];

                $responseObject = new \Magento\Object();
                $args = array('response_object' => $responseObject, 'selection' => $_selection);
                $this->_eventManager->dispatch('bundle_product_view_config', $args);
                if (is_array($responseObject->getAdditionalOptions())) {
                    foreach ($responseObject->getAdditionalOptions() as $o => $v) {
                        $selection[$o] = $v;
                    }
                }
                $option['selections'][$selectionId] = $selection;

                if (($_selection->getIsDefault() ||
                        $selectionCount == 1 && $_option->getRequired()) && $_selection->isSalable()
                ) {
                    $selected[$optionId][] = $selectionId;
                }
            }
            $options[$optionId] = $option;

            // Add attribute default value (if set)
            if ($preConfiguredFlag) {
                $configValue = $preConfiguredValues->getData('bundle_option/' . $optionId);
                if ($configValue) {
                    $defaultValues[$optionId] = $configValue;
                }
            }
        }
        $isFixedPrice = $this->getProduct()->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED;

        $config = array(
            'options' => $options,
            'selected' => $selected,
            'bundleId' => $currentProduct->getId(),
            'priceFormat' => $this->_localeFormat->getPriceFormat(),
            'basePrice' => $this->coreData->currency($currentProduct->getPrice(), false, false),
            'showIncludeTax' => $this->_taxData->displayPriceIncludingTax(),
            'finalBasePriceInclTax' => $this->coreData->currency(
                $this->_taxData->getPrice($currentProduct, $currentProduct->getFinalPrice(), true),
                false,
                false
            ),
            'finalBasePriceExclTax' => $this->coreData->currency($currentProduct->getFinalPrice(), false, false),
            'priceType' => $currentProduct->getPriceType(),
            'specialPrice' => $currentProduct->getSpecialPrice(),
            'includeTax' => $this->_taxData->priceIncludesTax() ? 'true' : 'false',
            'isFixedPrice' => $isFixedPrice,
            'isMAPAppliedDirectly' => $this->_catalogData->canApplyMsrp($this->getProduct(), null, false)
        );

        $config['finalPrice'] = $this->_taxData->displayPriceIncludingTax()
            ? $config['finalBasePriceInclTax']
            : $config['finalBasePriceExclTax'];

        if ($preConfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Get html for option
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return string
     */
    public function getOptionHtml($option)
    {
        $optionBlock = $this->getChildBlock($option->getType());
        if (!$optionBlock) {
            return __('There is no defined renderer for "%1" option type.', $option->getType());
        }
        return $optionBlock->setOption($option)->toHtml();
    }
}
