<?php
/**
 * Product initialization helper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;
use Magento\Framework\App\RequestInterface;

/**
 * Class Configurable
 */
class Configurable
{
    /** @var VariationHandler */
    protected $variationHandler;

    /** @var RequestInterface */
    protected $request;

    /** @var ConfigurableProduct */
    protected $productType;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var OptionValueInterfaceFactory
     */
    private $optionValueFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * Configurable constructor.
     * @param VariationHandler $variationHandler
     * @param ConfigurableProduct $productType
     * @param RequestInterface $request
     * @param AttributeFactory $attributeFactory
     * @param OptionValueInterfaceFactory $optionValueFactory
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        VariationHandler $variationHandler,
        ConfigurableProduct $productType,
        RequestInterface $request,
        AttributeFactory $attributeFactory,
        OptionValueInterfaceFactory $optionValueFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->variationHandler = $variationHandler;
        $this->productType = $productType;
        $this->request = $request;
        $this->attributeFactory = $attributeFactory;
        $this->optionValueFactory = $optionValueFactory;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * Initialize data for configurable product
     *
     * @param Helper $subject
     * @param ProductInterface $product
     * @return ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(Helper $subject, ProductInterface $product)
    {
        $attributes = $this->request->getParam('attributes');

        if ($product->getTypeId() !== ConfigurableProduct::TYPE_CODE && empty($attributes)) {
            return $product;
        }

        $setId = $this->request->getPost('new-variations-attribute-set-id');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        $extensionAttributes = $product->getExtensionAttributes();
        $this->setConfigurableOptions($extensionAttributes);

        $product->setNewVariationsAttributeSetId($setId);

        $this->setLinkedProducts($product, $extensionAttributes);
        $product->setCanSaveConfigurableAttributes(
            (bool) $this->request->getPost('affect_configurable_product_attributes')
        );
        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }

    /**
     * Set configurable product options
     *
     * @param ProductExtensionInterface $extensionAttributes
     * @return void
     */
    private function setConfigurableOptions(ProductExtensionInterface $extensionAttributes) {
        $options = [];
        $configurableAttributesData = $this->request->getPost('product')['configurable_attributes_data'];
        foreach ($configurableAttributesData as $item) {
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
            $attribute = $this->attributeFactory->create();
            $eavAttribute = $this->productAttributeRepository->get($item[Attribute::KEY_ATTRIBUTE_ID]);

            if (!$this->productType->canUseAttribute($eavAttribute)) {
                throw new \InvalidArgumentException(
                    'Provided attribute can not be used with configurable product'
                );
            }
            $this->updateAttributeData($attribute, $item);
            $options[] = $attribute;
        }

        $extensionAttributes->setConfigurableProductOptions($options);
    }

    /**
     * Update attribute data
     *
     * @param OptionInterface $attribute
     * @param array $item
     * @return void
     */
    private function updateAttributeData(OptionInterface $attribute, array $item)
    {
        $values = [];
        foreach ($item['values'] as $value) {
            /** @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterface $option */
            $option = $this->optionValueFactory->create();
            $option->setValueIndex($value['value_index']);
            $values[] = $option;
        }
        $attribute->setData(array_replace_recursive((array)$attribute->getData(), $item));
        $attribute->setValues($values);
    }

    /**
     * Relate simple products to configurable
     *
     * @param ProductInterface $product
     * @param ProductExtensionInterface $extensionAttributes
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setLinkedProducts(ProductInterface $product, ProductExtensionInterface $extensionAttributes)
    {
        $associatedProductIds = $this->request->getPost('associated_product_ids', []);
        $variationsMatrix = $this->request->getParam('variations-matrix', []);
        if (!empty($variationsMatrix)) {
            $generatedProductIds = $this->variationHandler->generateSimpleProducts($product, $variationsMatrix);
            $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
        }
        $extensionAttributes->setConfigurableProductLinks(array_filter($associatedProductIds));
    }
}
