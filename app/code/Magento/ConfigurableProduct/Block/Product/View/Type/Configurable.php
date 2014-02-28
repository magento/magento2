<?php
/**
 * Catalog super product configurable part block
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Model\Product\PriceModifierInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * Prices
     *
     * @var array
     */
    protected $_prices      = array();

    /**
     * Prepared prices
     *
     * @var array
     */
    protected $_resPrices   = array();

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct = null;

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\Price
     */
    protected $priceHelper;

    /**
     * @var \Magento\Catalog\Model\Product\PriceModifierInterface
     */
    protected $priceModifier;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Catalog\Helper\Product\Compare $compareProduct
     * @param \Magento\Theme\Helper\Layout $layoutHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Helper\Product\Price $priceHelper
     * @param \Magento\Catalog\Model\Product\PriceModifierInterface $priceModifier
     * @param array $data
     * @param array $priceBlockTypes
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Catalog\Helper\Product\Compare $compareProduct,
        \Magento\Theme\Helper\Layout $layoutHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Helper\Product\Price $priceHelper,
        PriceModifierInterface $priceModifier,
        array $data = array(),
        array $priceBlockTypes = array()
    ) {
        $this->_imageHelper = $imageHelper;
        $this->_catalogProduct = $catalogProduct;
        $this->_jsonEncoder = $jsonEncoder;
        $this->priceHelper = $priceHelper;
        $this->priceModifier = $priceModifier;
        parent::__construct(
            $context,
            $catalogConfig,
            $registry,
            $taxData,
            $catalogData,
            $mathRandom,
            $cartHelper,
            $wishlistHelper,
            $compareProduct,
            $layoutHelper,
            $imageHelper,
            $arrayUtils,
            $data,
            $priceBlockTypes
        );
        $this->_isScopePrivate = true;
    }

    /**
     * Get allowed attributes
     *
     * @return array
     */
    public function getAllowAttributes()
    {
        return $this->getProduct()->getTypeInstance()
            ->getConfigurableAttributes($this->getProduct());
    }

    /**
     * Check if allowed attributes have options
     *
     * @return bool
     */
    public function hasOptions()
    {
        $attributes = $this->getAllowAttributes();
        if (count($attributes)) {
            foreach ($attributes as $attribute) {
                /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
                if ($attribute->getData('prices')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get Allowed Products
     *
     * @return array
     */
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $skipSaleableCheck = $this->_catalogProduct->getSkipSaleableCheck();
            $allProducts = $this->getProduct()->getTypeInstance()
                ->getUsedProducts($this->getProduct(), null);
            foreach ($allProducts as $product) {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    /**
     * Retrieve current store
     *
     * @return \Magento\Core\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Returns additional values for js config, con be overridden by descendants
     *
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        return array();
    }

    /**
     * Composes configuration for js
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getJsonConfig()
    {
        $attributes = array();
        $options    = array();
        $store      = $this->getCurrentStore();
        $taxHelper  = $this->_taxData;
        $currentProduct = $this->getProduct();
        $preConfiguredValues = null;

        $preConfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preConfiguredFlag) {
            $preConfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }

        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $image = $this->_imageHelper->init($product, 'image');

            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
                !$product->getImage() || $product->getImage() === 'no_selection'
                    ? $options['images'][$productAttributeId][$attributeValue][$productId] = null
                    : $options['images'][$productAttributeId][$attributeValue][$productId] = (string)$image;
            }
        }

        $this->_resPrices = array(
            $this->_preparePrice($currentProduct->getFinalPrice())
        );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = array(
                'id'        => $productAttribute->getId(),
                'code'      => $productAttribute->getAttributeCode(),
                'label'     => $attribute->getLabel(),
                'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    $currentProduct->setConfigurablePrice($this->priceModifier->modifyPrice(
                        $currentProduct->getConfigurablePrice(), $currentProduct
                    ));
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }

                    $info['options'][] = array(
                        'id'        => $value['value_index'],
                        'label'     => $value['label'],
                        'price'     => $configurablePrice,
                        'oldPrice'  => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
                        'products'  => $productsIndex,
                    );
                    $optionPrices[] = $configurablePrice;
                }
            }
            /**
             * Prepare formatted values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional-$optionPrice));
                }
            }
            if ($this->_validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preConfiguredFlag) {
                $configValue = $preConfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }

        if (!$this->priceHelper->getCustomer() && $this->_coreRegistry->registry('current_customer')) {
            $this->priceHelper->setCustomer($this->_coreRegistry->registry('current_customer'));
        }

        $_request = $this->priceHelper->getRateRequest(false, false, false);
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $defaultTax = $this->priceHelper->getRate($_request);

        $_request = $this->priceHelper->getRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $currentTax = $this->priceHelper->getRate($_request);

        $taxConfig = array(
            'includeTax'        => $taxHelper->priceIncludesTax(),
            'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'    => $taxHelper->displayBothPrices(),
            'defaultTax'        => $defaultTax,
            'currentTax'        => $currentTax,
            'inclTaxTitle'      => __('Incl. Tax')
        );

        $config = array(
            'attributes'        => $attributes,
            'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
            'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId'         => $currentProduct->getId(),
            'chooseText'        => __('Choose an Option...'),
            'taxConfig'         => $taxConfig,
            'images'            => $options['images'],
        );

        if ($preConfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->_jsonEncoder->encode($config);
    }

    /**
     * Validating of super product option value
     *
     * @param int $attributeId
     * @param array $value
     * @param array $options
     * @return bool
     */
    protected function _validateAttributeValue($attributeId, &$value, &$options)
    {
        if (isset($options[$attributeId][$value['value_index']])) {
            return true;
        }

        return false;
    }

    /**
     * Validation of super product option
     *
     * @param array $info
     * @return bool
     */
    protected function _validateAttributeInfo(&$info)
    {
        if (count($info['options']) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Calculation real price
     *
     * @param float $price
     * @param bool $isPercent
     * @return string
     */
    protected function _preparePrice($price, $isPercent = false)
    {
        if ($isPercent && !empty($price)) {
            $price = $this->getProduct()->getFinalPrice() * $price / 100;
        }

        return $this->_registerJsPrice($this->_convertPrice($price, true));
    }

    /**
     * Calculation price before special price
     *
     * @param float $price
     * @param bool $isPercent
     * @return string
     */
    protected function _prepareOldPrice($price, $isPercent = false)
    {
        if ($isPercent && !empty($price)) {
            $price = $this->getProduct()->getPrice() * $price / 100;
        }

        return $this->_registerJsPrice($this->_convertPrice($price, true));
    }

    /**
     * Replace ',' on '.' for js
     *
     * @param float $price
     * @return string
     */
    protected function _registerJsPrice($price)
    {
        return str_replace(',', '.', $price);
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @param bool $round
     * @return float
     */
    protected function _convertPrice($price, $round = false)
    {
        if (empty($price)) {
            return 0;
        }

        $price = $this->getCurrentStore()->convertPrice($price);
        if ($round) {
            $price = $this->getCurrentStore()->roundPrice($price);
        }

        return $price;
    }
}
