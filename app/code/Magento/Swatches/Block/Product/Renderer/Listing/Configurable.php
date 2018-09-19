<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\Product\Renderer\Listing;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\SwatchAttributesProvider;

/**
 * Swatch renderer block in Category page
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    /**
     * @var \Magento\Framework\Locale\Format
     */
    private $localeFormat;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices
     */
    private $variationPrices;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param array $data
     * @param SwatchAttributesProvider|null $swatchAttributesProvider
     * @param \Magento\Framework\Locale\Format|null $localeFormat
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices|null $variationPrices
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        array $data = [],
        SwatchAttributesProvider $swatchAttributesProvider = null,
        \Magento\Framework\Locale\Format $localeFormat = null,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices $variationPrices = null
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data,
            $swatchAttributesProvider
        );
        $this->localeFormat = $localeFormat ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Locale\Format::class
        );
        $this->variationPrices = $variationPrices ?: ObjectManager::getInstance()->get(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices::class
        );
    }

    /**
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->_template;
    }

    /**
     * Render block hook
     *
     * Produce and return block's html output
     *
     * @return string
     * @since 100.1.5
     */
    protected function _toHtml()
    {
        $output = '';
        if ($this->isProductHasSwatchAttribute()) {
            $output = parent::_toHtml();
        }

        return $output;
    }

    /**
     * @return array
     */
    protected function getSwatchAttributesData()
    {
        $result = [];
        $swatchAttributeData = parent::getSwatchAttributesData();
        foreach ($swatchAttributeData as $attributeId => $item) {
            if (!empty($item['used_in_product_listing'])) {
                $result[$attributeId] = $item;
            }
        }
        return $result;
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $this->unsetData('allow_products');
        return parent::getJsonConfig();
    }
    
    /**
     * Composes configuration for js price format
     *
     * @return string
     */
    public function getPriceFormatJson()
    {
        return $this->jsonEncoder->encode($this->localeFormat->getPriceFormat());
    }

    /**
     * Composes configuration for js price
     *
     * @return string
     */
    public function getPricesJson()
    {
        return $this->jsonEncoder->encode(
            $this->variationPrices->getFormattedPrices($this->getProduct()->getPriceInfo())
        );
    }

    /**
     * Do not load images for Configurable product with swatches due to its loaded by request
     *
     * @return array
     * @since 100.2.0
     */
    protected function getOptionImages()
    {
        return [];
    }

    /**
     * Add images to result json config in case of Layered Navigation is used
     *
     * @return array
     * @since 100.2.0
     */
    protected function _getAdditionalConfig()
    {
        $config = parent::_getAdditionalConfig();
        if (!empty($this->getRequest()->getQuery()->toArray())) {
            $config['preSelectedGallery'] = $this->getProductVariationWithMedia(
                $this->getProduct(),
                $this->getRequest()->getQuery()->toArray()
            );
        }

        return $config;
    }

    /**
     * Get product images for chosen variation based on selected product attributes
     *
     * @param Product $configurableProduct
     * @param array $additionalAttributes
     * @return array
     */
    private function getProductVariationWithMedia(
        Product $configurableProduct,
        array $additionalAttributes = []
    ) {
        $configurableAttributes = $this->getLayeredAttributesIfExists($configurableProduct, $additionalAttributes);
        if (!$configurableAttributes) {
            return [];
        }

        $product = $this->swatchHelper->loadVariationByFallback($configurableProduct, $configurableAttributes);

        return $product ? $this->swatchHelper->getProductMediaGallery($product) : [];
    }

    /**
     * Get product attributes which uses in layered navigation and present for given configurable product
     *
     * @param Product $configurableProduct
     * @param array $additionalAttributes
     * @return array
     */
    private function getLayeredAttributesIfExists(Product $configurableProduct, array $additionalAttributes)
    {
        $configurableAttributes = $this->swatchHelper->getAttributesFromConfigurable($configurableProduct);

        $layeredAttributes = [];

        $configurableAttributes = array_map(function ($attribute) {
            return $attribute->getAttributeCode();
        }, $configurableAttributes);

        $commonAttributeCodes = array_intersect(
            $configurableAttributes,
            array_keys($additionalAttributes)
        );

        foreach ($commonAttributeCodes as $attributeCode) {
            $layeredAttributes[$attributeCode] = $additionalAttributes[$attributeCode];
        }

        return $layeredAttributes;
    }
}
