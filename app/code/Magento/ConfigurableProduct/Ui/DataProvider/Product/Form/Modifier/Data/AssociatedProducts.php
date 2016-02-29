<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Framework\UrlInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class AssociatedProducts
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ConfigurableType
     */
    protected $configurableType;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var array
     */
    protected $productMatrix = [];

    /**
     * @var array
     */
    protected $productAttributes = [];

    /**
     * @var array
     */
    protected $productIds = [];

    /**
     * @var VariationMatrix
     */
    protected $variationMatrix;

    /**
    * @var UrlInterface
    */
    protected $urlBuilder;

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ConfigurableType $configurableType
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param VariationMatrix $variationMatrix
     * @param CurrencyInterface $localeCurrency
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ConfigurableType $configurableType,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        VariationMatrix $variationMatrix,
        CurrencyInterface $localeCurrency,
        JsonHelper $jsonHelper
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->variationMatrix = $variationMatrix;
        $this->localeCurrency = $localeCurrency;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return array
     */
    public function getProductMatrix()
    {
        if ($this->productMatrix === []) {
            $this->prepareVariations();
        }
        return $this->productMatrix;
    }

    /**
     * @return array
     */
    public function getProductAttributes()
    {
        if ($this->productAttributes === []) {
            $this->prepareVariations();
        }
        return $this->productAttributes;
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        if ($this->productIds === []) {
            $this->prepareVariations();
        }
        return $this->productIds;
    }

    /**
     * @return array
     */
    public function getProductAttributesIds()
    {
        $result = [];

        foreach ($this->getProductAttributes() as $attribute) {
            $result[] = $attribute['id'];
        }

        return $result;
    }

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
                                'position' => $attribute->getPosition(),
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
                        'quantity_and_stock_status' => ['qty' => $this->getProductStockQty($product)],
                        'price' => $currency->toCurrency(sprintf("%f", $price), ['display' => false]),
                        'price_string' => $currency->toCurrency(sprintf("%f", $price)),
                        'price_currency' => $this->locator->getStore()->getBaseCurrency()->getCurrencySymbol(),
                        'configurable_attribute' => $this->getJsonConfigurableAttributes($variationOptions),
                        'weight' => $product->getWeight(),
                        'status' => $product->getStatus(),
                        'canEdit' => 0,
                        'newProduct' => 0,
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
     * @param array $options
     * @return string
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
     * Retrieve actual list of associated products, array key is obtained from varying attributes values
     *
     * @return Product[]
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
     */
    protected function getUsedAttributes()
    {
        return $this->configurableType->getUsedProductAttributes($this->locator->getProduct());
    }

    /**
     * @param Product $product
     * @return float
     */
    protected function getProductStockQty(Product $product)
    {
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }

    /**
     * Retrieve all possible attribute values combinations
     *
     * @return array
     */
    protected function getVariations()
    {
        return $this->variationMatrix->getVariations($this->getAttributes());
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     */
    protected function getAttributes()
    {
        return (array)$this->configurableType->getConfigurableAttributesAsArray($this->locator->getProduct());
    }
}
