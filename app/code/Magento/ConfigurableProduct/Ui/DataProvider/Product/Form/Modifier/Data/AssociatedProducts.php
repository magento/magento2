<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;

/**
 * Associated products helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var Escaper
     */
    private $escaper;

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
     * @param Escaper|null $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        ImageHelper $imageHelper,
        ?Escaper $escaper = null
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
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Get variations product matrix
     *
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
     * Get product attributes
     *
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
     * Get ids of associated products
     *
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
     * Get ids of product attributes
     *
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

    /**
     * Get codes of product attributes
     *
     * @return array
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
     * @throws CurrencyException
     */
    protected function prepareVariations(): void
    {
        $productMatrix = $attributes = [];
        $variants = $this->getVariantAttributeComposition();
        $productIds = [];
        foreach ($this->getAssociatedProducts() as $product) {
            $childProductOptions = [];
            $productIds[] = $product->getId();
            foreach ($variants[$product->getId()] as $attributeComposition) {
                $childProductOptions[] = $this->buildChildProductOption($attributeComposition);

                /** @var AbstractAttribute $attribute */
                $attribute = $attributeComposition['attribute'];
                if (!isset($attributes[$attribute->getAttributeId()])) {
                    $attributes[$attribute->getAttributeId()] = $this->buildAttributeDetails($attribute);
                }
                $variationOption = [
                    'attribute_code' => $attribute->getAttributeCode(),
                    'attribute_label' => $attribute->getStoreLabel(0),
                    'id' => $attributeComposition['value_id'],
                    'label' => $attributeComposition['label'],
                    'value' => $attributeComposition['value_id']
                ];
                $attributes[$attribute->getAttributeId()]['chosen'][$attributeComposition['value_id']] =
                    $variationOption;
            }
            $productMatrix[] = $this->buildChildProductDetails($product, $childProductOptions);
        }

        $this->productMatrix = $productMatrix;
        $this->productIds = $productIds;
        $this->productAttributes = array_values($attributes);
    }
    //phpcs: enable

    /**
     * Get JSON string that contains attribute code and value
     *
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
     * Prepares text list of used attributes
     *
     * @param array $options
     * @return string
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
     * Retrieve qty of product
     *
     * @param Product $product
     * @return float
     */
    protected function getProductStockQty(Product $product)
    {
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId())->getQty();
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

    /**
     * Prepare attribute details for child product configuration
     *
     * @param AbstractAttribute $attribute
     * @return array
     */
    private function buildAttributeDetails(AbstractAttribute $attribute): array
    {
        $configurableAttributes = $this->getAttributes();
        $details = [
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel(),
            'id' => $attribute->getAttributeId(),
            'position' => $configurableAttributes[$attribute->getAttributeId()]['position'],
            'chosen' => []
        ];

        $options = $attribute->usesSource() ? $attribute->getSource()->getAllOptions() : [];
        foreach ($options as $option) {
            if (!empty($option['value'])) {
                $details['options'][$option['value']] = [
                    'attribute_code' => $attribute->getAttributeCode(),
                    'attribute_label' => $attribute->getStoreLabel(0),
                    'id' => $option['value'],
                    'label' => $option['label'],
                    'value' => $option['value']
                ];
            }
        }

        return $details;
    }

    /**
     * Generate configurable product child option
     *
     * @param array $attributeDetails
     * @return array
     */
    private function buildChildProductOption(array $attributeDetails): array
    {
        return [
            'attribute_code' => $attributeDetails['attribute']->getAttributeCode(),
            'attribute_label' => $attributeDetails['attribute']->getStoreLabel(0),
            'id' => $attributeDetails['value_id'],
            'label' => $attributeDetails['label'],
            'value' => $attributeDetails['value_id']
        ];
    }

    /**
     * Get label for a specific value of an attribute.
     *
     * @param AbstractAttribute $attribute
     * @param mixed $valueId
     * @return string
     */
    private function extractAttributeValueLabel(AbstractAttribute $attribute, mixed $valueId): string
    {
        foreach ($attribute->getOptions() as $attributeOption) {
            if ($attributeOption->getValue() == $valueId) {
                return $attributeOption->getLabel();
            }
        }

        return '';
    }

    /**
     * Create child product details
     *
     * @param Product $product
     * @param array $childProductOptions
     * @return array
     * @throws CurrencyException
     */
    private function buildChildProductDetails(Product $product, array $childProductOptions): array
    {
        $currency = $this->localeCurrency->getCurrency($this->locator->getBaseCurrencyCode());
        return [
            'id' => $product->getId(),
            'product_link' => '<a href="' .
                $this->urlBuilder->getUrl('catalog/product/edit', ['id' => $product->getId()])
                . '" target="_blank">' . $this->escaper->escapeHtml($product->getName()) . '</a>',
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'qty' => $this->getProductStockQty($product),
            'price' => $product->getPrice(),
            'price_string' => $currency->toCurrency(sprintf("%f", $product->getPrice())),
            'price_currency' => $this->locator->getStore()->getBaseCurrency()->getCurrencySymbol(),
            'configurable_attribute' => $this->getJsonConfigurableAttributes($childProductOptions),
            'weight' => $product->getWeight(),
            'status' => $product->getStatus(),
            'variationKey' => $this->getVariationKey($childProductOptions),
            'canEdit' => 0,
            'newProduct' => 0,
            'attributes' => $this->getTextAttributes($childProductOptions), //here be the problem
            'thumbnail_image' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
        ];
    }

    /**
     * Retrieves attributes that are used for configurable product variations
     *
     * @return array
     */
    private function getVariantAttributeComposition(): array
    {
        $variants = [];
        foreach ($this->_getAssociatedProducts() as $product) {
            /* @var $attribute AbstractAttribute */
            foreach ($this->getUsedAttributes() as $attribute) {
                $variants[$product->getId()][$attribute->getAttributeCode()] =
                    [
                        'value_id' => $product->getData($attribute->getAttributeCode()),
                        'label' => $this->extractAttributeValueLabel(
                            $attribute,
                            $product->getData($attribute->getAttributeCode())
                        ),
                        'attribute' => $attribute
                    ];
            }
        }

        return $variants;
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
}
