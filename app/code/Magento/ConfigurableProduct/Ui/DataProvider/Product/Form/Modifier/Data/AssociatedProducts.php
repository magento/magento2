<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\UrlInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class AssociatedProducts
{
    /**
     * @var LocatorInterface
     * @since 2.1.0
     */
    protected $locator;

    /**
     * @var ConfigurableType
     * @since 2.1.0
     */
    protected $configurableType;

    /**
     * @var ProductRepositoryInterface
     * @since 2.1.0
     */
    protected $productRepository;

    /**
     * @var StockRegistryInterface
     * @since 2.1.0
     */
    protected $stockRegistry;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $productMatrix = [];

    /**
     * @var array
     * @since 2.1.0
     */
    protected $productAttributes = [];

    /**
     * @var array
     * @since 2.1.0
     */
    protected $productIds = [];

    /**
     * @var VariationMatrix
     * @since 2.1.0
     */
    protected $variationMatrix;

    /**
     * @var UrlInterface
     * @since 2.1.0
     */
    protected $urlBuilder;

    /**
     * @var CurrencyInterface
     * @since 2.1.0
     */
    protected $localeCurrency;

    /**
     * @var JsonHelper
     * @since 2.1.0
     */
    protected $jsonHelper;

    /**
     * @var ImageHelper
     * @since 2.1.0
     */
    protected $imageHelper;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ConfigurableType $configurableType
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param VariationMatrix $variationMatrix
     * @param CurrencyInterface $localeCurrency
     * @param JsonHelper $jsonHelper
     * @param ImageHelper $imageHelper
     * @since 2.1.0
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ConfigurableType $configurableType,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        VariationMatrix $variationMatrix,
        CurrencyInterface $localeCurrency,
        JsonHelper $jsonHelper,
        ImageHelper $imageHelper
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->variationMatrix = $variationMatrix;
        $this->localeCurrency = $localeCurrency;
        $this->jsonHelper = $jsonHelper;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get variations product matrix
     *
     * @return array
     * @since 2.1.0
     */
    public function getProductMatrix()
    {
        if ($this->productMatrix === []) {
            $this->prepareVariations();
        }
        return $this->productMatrix;
    }

    /**
     * Get product attributes
     *
     * @return array
     * @since 2.1.0
     */
    public function getProductAttributes()
    {
        if ($this->productAttributes === []) {
            $this->prepareVariations();
        }
        return $this->productAttributes;
    }

    /**
     * Get ids of associated products
     *
     * @return array
     * @since 2.1.0
     */
    public function getProductIds()
    {
        if ($this->productIds === []) {
            $this->prepareVariations();
        }
        return $this->productIds;
    }

    /**
     * Get ids of product attributes
     *
     * @return array
     * @since 2.1.0
     */
    public function getProductAttributesIds()
    {
        $result = [];

        foreach ($this->getProductAttributes() as $attribute) {
            $result[] = $attribute['id'];
        }

        return $result;
    }

    /**
     * Get codes of product attributes
     *
     * @return array
     * @since 2.1.0
     */
    public function getProductAttributesCodes()
    {
        $result = [];

        foreach ($this->getProductAttributes() as $attribute) {
            $result[] = $attribute['code'];
        }

        return $result;
    }

    /**
     * Get full data of configurable product attributes
     *
     * @return array
     * @since 2.1.0
     */
    public function getConfigurableAttributesData()
    {
        $result = [];

        foreach ($this->getProductAttributes() as $attribute) {
            $result[$attribute['id']] = [
                'attribute_id' => $attribute['id'],
                'code' => $attribute['code'],
                'label' => $attribute['label'],
                'position' => $attribute['position'],
            ];

            foreach ($attribute['chosen'] as $chosenOption) {
                $result[$attribute['id']]['values'][$chosenOption['value']] = [
                    'include' => 1,
                    'value_index' => $chosenOption['value'],
                ];
            }
        }

        return $result;
    }

    /**
     * Prepare variations
     *
     * @return void
     * @throws \Zend_Currency_Exception
     * @since 2.1.0
     */
    protected function prepareVariations()
    {
        $variations = $this->getVariations();
        $productMatrix = [];
        $attributes = [];
        $productIds = [];
        if ($variations) {
            $usedProductAttributes = $this->getUsedAttributes();
            $productByUsedAttributes = $this->getAssociatedProducts();
            $currency = $this->localeCurrency->getCurrency($this->locator->getBaseCurrencyCode());
            $configurableAttributes = $this->getAttributes();
            foreach ($variations as $variation) {
                $attributeValues = [];
                foreach ($usedProductAttributes as $attribute) {
                    $attributeValues[$attribute->getAttributeCode()] = $variation[$attribute->getId()]['value'];
                }
                $key = implode('-', $attributeValues);
                if (isset($productByUsedAttributes[$key])) {
                    $product = $productByUsedAttributes[$key];
                    $price = $product->getPrice();
                    $variationOptions = [];
                    foreach ($usedProductAttributes as $attribute) {
                        if (!isset($attributes[$attribute->getAttributeId()])) {
                            $attributes[$attribute->getAttributeId()] = [
                                'code' => $attribute->getAttributeCode(),
                                'label' => $attribute->getStoreLabel(),
                                'id' => $attribute->getAttributeId(),
                                'position' => $configurableAttributes[$attribute->getAttributeId()]['position'],
                                'chosen' => [],
                            ];
                            foreach ($attribute->getOptions() as $option) {
                                if (!empty($option->getValue())) {
                                    $attributes[$attribute->getAttributeId()]['options'][$option->getValue()] = [
                                        'attribute_code' => $attribute->getAttributeCode(),
                                        'attribute_label' => $attribute->getStoreLabel(0),
                                        'id' => $option->getValue(),
                                        'label' => $option->getLabel(),
                                        'value' => $option->getValue(),
                                    ];
                                }
                            }
                        }
                        $optionId = $variation[$attribute->getId()]['value'];
                        $variationOption = [
                            'attribute_code' => $attribute->getAttributeCode(),
                            'attribute_label' => $attribute->getStoreLabel(0),
                            'id' => $optionId,
                            'label' => $variation[$attribute->getId()]['label'],
                            'value' => $optionId,
                        ];
                        $variationOptions[] = $variationOption;
                        $attributes[$attribute->getAttributeId()]['chosen'][$optionId] = $variationOption;
                    }

                    $productMatrix[] = [
                        'id' => $product->getId(),
                        'product_link' => '<a href="' . $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $product->getId()]
                        ) . '" target="_blank">' . $product->getName() . '</a>',
                        'sku' => $product->getSku(),
                        'name' => $product->getName(),
                        'qty' => $this->getProductStockQty($product),
                        'price' => $price,
                        'price_string' => $currency->toCurrency(sprintf("%f", $price)),
                        'price_currency' => $this->locator->getStore()->getBaseCurrency()->getCurrencySymbol(),
                        'configurable_attribute' => $this->getJsonConfigurableAttributes($variationOptions),
                        'weight' => $product->getWeight(),
                        'status' => $product->getStatus(),
                        'variationKey' => $this->getVariationKey($variationOptions),
                        'canEdit' => 0,
                        'newProduct' => 0,
                        'attributes' => $this->getTextAttributes($variationOptions),
                        'thumbnail_image' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
                    ];
                    $productIds[] = $product->getId();
                }
            }
        }

        $this->productMatrix = $productMatrix;
        $this->productIds = $productIds;
        $this->productAttributes = array_values($attributes);
    }

    /**
     * Get JSON string that contains attribute code and value
     *
     * @param array $options
     * @return string
     * @since 2.1.0
     */
    protected function getJsonConfigurableAttributes(array $options = [])
    {
        $result = [];

        foreach ($options as $option) {
            $result[$option['attribute_code']] = $option['value'];
        }

        return $this->jsonHelper->jsonEncode($result);
    }

    /**
     * Prepares text list of used attributes
     *
     * @param array $options
     * @return string
     * @since 2.1.0
     */
    protected function getTextAttributes(array $options = [])
    {
        $text = '';
        foreach ($options as $option) {
            if ($text) {
                $text .= ', ';
            }
            $text .= $option['attribute_label'] . ': ' . $option['label'];
        }

        return $text;
    }

    /**
     * Get variation key
     *
     * @param array $options
     * @return string
     * @since 2.1.0
     */
    protected function getVariationKey(array $options = [])
    {
        $result = [];

        foreach ($options as $option) {
            $result[] = $option['value'];
        }

        asort($result);

        return implode('-', $result);
    }

    /**
     * Retrieve actual list of associated products, array key is obtained from varying attributes values
     *
     * @return Product[]
     * @since 2.1.0
     */
    protected function getAssociatedProducts()
    {
        $productByUsedAttributes = [];
        foreach ($this->_getAssociatedProducts() as $product) {
            $keys = [];
            foreach ($this->getUsedAttributes() as $attribute) {
                /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                $keys[] = $product->getData($attribute->getAttributeCode());
            }
            $productByUsedAttributes[implode('-', $keys)] = $product;
        }
        return $productByUsedAttributes;
    }

    /**
     * Retrieve actual list of associated products (i.e. if product contains variations matrix form data
     * - previously saved in database relations are not considered)
     *
     * @return Product[]
     * @since 2.1.0
     */
    protected function _getAssociatedProducts()
    {
        $product = $this->locator->getProduct();
        $ids = $this->locator->getProduct()->getAssociatedProductIds();
        if ($ids === null) {
            // form data overrides any relations stored in database
            return $this->configurableType->getUsedProducts($product);
        }
        $products = [];
        foreach ($ids as $productId) {
            try {
                $products[] = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }
        return $products;
    }

    /**
     * Get used product attributes
     *
     * @return array
     * @since 2.1.0
     */
    protected function getUsedAttributes()
    {
        return $this->configurableType->getUsedProductAttributes($this->locator->getProduct());
    }

    /**
     * Retrieve qty of product
     *
     * @param Product $product
     * @return float
     * @since 2.1.0
     */
    protected function getProductStockQty(Product $product)
    {
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }

    /**
     * Retrieve all possible attribute values combinations
     *
     * @return array
     * @since 2.1.0
     */
    protected function getVariations()
    {
        return $this->variationMatrix->getVariations($this->getAttributes());
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     * @since 2.1.0
     */
    protected function getAttributes()
    {
        return (array)$this->configurableType->getConfigurableAttributesAsArray($this->locator->getProduct());
    }
}
