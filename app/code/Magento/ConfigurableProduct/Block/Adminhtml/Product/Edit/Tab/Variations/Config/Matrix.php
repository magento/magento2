<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Product variations matrix block
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
        parent::__construct($context, $data);
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
     * Retrieve actual list of associated products (i.e. if product contains variations matrix form data
     * - previously saved in database relations are not considered)
     *
     * @return Product[]
     */
    protected function _getAssociatedProducts()
    {
        $product = $this->getProduct();
        $ids = $this->getProduct()->getAssociatedProductIds();
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
     * Prepare product variations.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return void
     * TODO: move to class
     */
    protected function prepareVariations()
    {
        $variations = $this->getVariations();
        $productMatrix = [];
        $attributes = [];
        if ($variations) {
            $usedProductAttributes = $this->getUsedAttributes();
            $productByUsedAttributes = $this->getAssociatedProducts();
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
                        list($attributes, $variationOptions) = $this->prepareAttributes(
                            $attributes,
                            $attribute,
                            $configurableAttributes,
                            $variation,
                            $variationOptions
                        );
                    }

                    $productMatrix[] = [
                        'productId' => $product->getId(),
                        'images' => [
                            'preview' => $this->image->init($product, 'product_thumbnail_image')->getUrl()
                        ],
                        'sku' => $product->getSku(),
                        'name' => $product->getName(),
                        'quantity' => $this->getProductStockQty($product),
                        'price' => $price,
                        'options' => $variationOptions,
                        'weight' => $product->getWeight(),
                        'status' => $product->getStatus(),
                        '__disableTmpl' => true,
                    ];
                }
            }
        }
        $this->productMatrix = $productMatrix;
        $this->productAttributes = array_values($attributes);
    }

    /**
     * Prepare attributes.
     *
     * @param array $attributes
     * @param object $attribute
     * @param array $configurableAttributes
     * @param array $variation
     * @param array $variationOptions
     * @return array
     */
    private function prepareAttributes(
        array $attributes,
        $attribute,
        array $configurableAttributes,
        array $variation,
        array $variationOptions
    ): array {
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
                    $attributes[$attribute->getAttributeId()]['options'][] = [
                        'attribute_code' => $attribute->getAttributeCode(),
                        'attribute_label' => $attribute->getStoreLabel(0),
                        'id' => $option->getValue(),
                        'label' => $option->getLabel(),
                        'value' => $option->getValue(),
                        '__disableTmpl' => true,
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
            '__disableTmpl' => true,
        ];
        $variationOptions[] = $variationOption;
        $attributes[$attribute->getAttributeId()]['chosen'][] = $variationOption;

        return [$attributes, $variationOptions];
    }
}
