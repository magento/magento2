<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Product variations matrix block
 * All disableTmpl flag are required here for configurable products
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Matrix extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_configurableType;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix
     */
    protected $variationMatrix;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $image;

    /**
     * @var null|array
     */
    private $productMatrix;

    /**
     * @var null|array
     */
    private $productAttributes;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var LocatorInterface
     * @since 100.1.0
     */
    protected $locator;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $variationMatrix
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $image
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param LocatorInterface $locator
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $variationMatrix,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $image,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        LocatorInterface $locator,
        array $data = []
    ) {
        parent::__construct($context, $data, $data['jsonHelper'] ?? null, $data['directoryHelper'] ?? null);
        $this->_configurableType = $configurableType;
        $this->stockRegistry = $stockRegistry;
        $this->variationMatrix = $variationMatrix;
        $this->productRepository = $productRepository;
        $this->localeCurrency = $localeCurrency;
        $this->image = $image;
        $this->locator = $locator;
    }

    /**
     * Return currency symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->localeCurrency->getCurrency($this->_storeManager->getStore()->getBaseCurrencyCode())->getSymbol();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->locator->getProduct();
    }

    /**
     * Retrieve all possible attribute values combinations
     *
     * @return array
     */
    public function getVariations()
    {
        return $this->variationMatrix->getVariations($this->getAttributes());
    }

    /**
     * Retrieve data source for variations data
     *
     * @return string
     * @since 100.1.0
     */
    public function getProvider()
    {
        return $this->getData('config/provider');
    }

    /**
     * Retrieve configurable modal name
     *
     * @return string
     * @since 100.1.0
     */
    public function getModal()
    {
        return $this->getData('config/modal');
    }

    /**
     * Retrieve form name
     *
     * @return string
     * @since 100.1.0
     */
    public function getForm()
    {
        return $this->getData('config/form');
    }

    /**
     * Retrieve configurable modal name
     *
     * @return string
     * @since 100.1.0
     */
    public function getConfigurableModal()
    {
        return $this->getData('config/configurableModal');
    }

    /**
     * Get url for product edit
     *
     * @param string $id
     * @return string
     */
    public function getEditProductUrl($id)
    {
        return $this->getUrl('catalog/*/edit', ['id' => $id]);
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     */
    protected function getAttributes()
    {
        if (!$this->hasData('attributes')) {
            $attributes = (array)$this->_configurableType->getConfigurableAttributesAsArray($this->getProduct());
            $productData = (array)$this->getRequest()->getParam('product');
            if (isset($productData['configurable_attributes_data'])) {
                $configurableData = $productData['configurable_attributes_data'];
                foreach ($attributes as $key => $attribute) {
                    if (isset($configurableData[$key])) {
                        $attributes[$key] = array_replace_recursive($attribute, $configurableData[$key]);
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $attributes[$key]['values'] = array_merge(
                            isset($attribute['values']) ? $attribute['values'] : [],
                            isset($configurableData[$key]['values'])
                            ? array_filter($configurableData[$key]['values'])
                            : []
                        );
                    }
                }
            }
            $this->setData('attributes', $attributes);
        }
        return $this->getData('attributes');
    }

    /**
     * Get used product attributes
     *
     * @return array
     */
    protected function getUsedAttributes()
    {
        return $this->_configurableType->getUsedProductAttributes($this->getProduct());
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
     * Retrieve actual list of associated products (i.e. if product contains variations matrix form data
     * - previously saved in database relations are not considered)
     *
     * @return Product[]
     */
    protected function _getAssociatedProducts()
    {
        $product = $this->getProduct();
        $ids = $product->getAssociatedProductIds();
        if ($ids === null) {
            // form data overrides any relations stored in database
            return $this->_configurableType->getUsedProducts($product);
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
     * Get url to upload files
     *
     * @return string
     */
    public function getImageUploadUrl()
    {
        return $this->getUrl('catalog/product_gallery/upload');
    }

    /**
     * Return product qty.
     *
     * @param Product $product
     * @return float
     */
    public function getProductStockQty(Product $product)
    {
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }

    /**
     * Return variation wizard.
     *
     * @param array $initData
     * @return string
     */
    public function getVariationWizard($initData)
    {
        /** @var \Magento\Ui\Block\Component\StepsWizard $wizardBlock */
        $wizardBlock = $this->getChildBlock($this->getData('config/nameStepWizard'));
        if ($wizardBlock) {
            $wizardBlock->setInitData($initData);
            return $wizardBlock->toHtml();
        }
        return '';
    }

    /**
     * Return product configuration matrix.
     *
     * @return array|null
     */
    public function getProductMatrix()
    {
        if ($this->productMatrix === null) {
            $this->prepareVariations();
        }
        return $this->productMatrix;
    }

    /**
     * Return product attributes.
     *
     * @return array|null
     */
    public function getProductAttributes()
    {
        if ($this->productAttributes === null) {
            $this->prepareVariations();
        }

        return $this->productAttributes;
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
            'chosen' => [],
            '__disableTmpl' => true
        ];

        foreach ($attribute->getOptions() as $option) {
            if ($option->getValue()) {
                $details['options'][] = [
                    'attribute_code' => $attribute->getAttributeCode(),
                    'attribute_label' => $attribute->getStoreLabel(0),
                    'id' => $option->getValue(),
                    'label' => $option->getLabel(),
                    'value' => $option->getValue(),
                    '__disableTmpl' => true,
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
            'value' => $attributeDetails['value_id'],
            '__disableTmpl' => true,
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
     * Prepare product variations.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return void
     * TODO: move to class
     */
    protected function prepareVariations()
    {
        $productMatrix = $attributes = [];
        $variants = $this->getVariantAttributeComposition();
        foreach (array_reverse($this->getAssociatedProducts()) as $product) {
            $childProductOptions = [];
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
                    'value' => $attributeComposition['value_id'],
                    '__disableTmpl' => true,
                ];
                $attributes[$attribute->getAttributeId()]['chosen'][] = $variationOption;
            }
            $productMatrix[] = $this->buildChildProductDetails($product, $childProductOptions);
        }

        $this->productMatrix = $productMatrix;
        $this->productAttributes = array_values($attributes);
    }

    /**
     * Create child product details
     *
     * @param Product $product
     * @param array $childProductOptions
     * @return array
     */
    private function buildChildProductDetails(Product $product, array $childProductOptions): array
    {
        return [
            'productId' => $product->getId(),
            'images' => [
                'preview' => $this->image->init($product, 'product_thumbnail_image')->getUrl()
            ],
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'quantity' => $this->getProductStockQty($product),
            'price' => $product->getPrice(),
            'options' => $childProductOptions,
            'weight' => $product->getWeight(),
            'status' => $product->getStatus(),
            '__disableTmpl' => true,
        ];
    }
}
