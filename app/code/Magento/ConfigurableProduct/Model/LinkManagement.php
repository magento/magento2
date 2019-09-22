<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Configurable product link management.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagement implements \Magento\ConfigurableProduct\Api\LinkManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $configurableType;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Product\Options\Factory;
     */
    private $optionsFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    private $attributeFactory;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory = null
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->configurableType = $configurableType;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->attributeFactory = $attributeFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function getChildren($sku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($product->getStoreId(), $product);

        $childrenList = [];
        /** @var \Magento\Catalog\Model\Product $child */
        foreach ($productTypeInstance->getUsedProducts($product) as $child) {
            $attributes = [];
            foreach ($child->getAttributes() as $attribute) {
                $attrCode = $attribute->getAttributeCode();
                $value = $child->getDataUsingMethod($attrCode) ?: $child->getData($attrCode);
                if (null !== $value) {
                    $attributes[$attrCode] = $value;
                }
            }
            $attributes['store_id'] = $child->getStoreId();
            /** @var \Magento\Catalog\Api\Data\ProductInterface $productDataObject */
            $productDataObject = $this->productFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productDataObject,
                $attributes,
                \Magento\Catalog\Api\Data\ProductInterface::class
            );
            $childrenList[] = $productDataObject;
        }
        return $childrenList;
    }

    /**
     * @inheritdoc
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function addChild($sku, $childSku)
    {
        $product = $this->productRepository->get($sku, true);
        $child = $this->productRepository->get($childSku);

        $childrenIds = array_values($this->configurableType->getChildrenIds($product->getId())[0]);
        if (in_array($child->getId(), $childrenIds)) {
            throw new StateException(__('The product is already attached.'));
        }

        $configurableProductOptions = $product->getExtensionAttributes()->getConfigurableProductOptions();
        if (empty($configurableProductOptions)) {
            throw new StateException(__("The parent product doesn't have configurable product options."));
        }

        $attributeData = [];
        foreach ($configurableProductOptions as $configurableProductOption) {
            $attributeCode = $configurableProductOption->getProductAttribute()->getAttributeCode();
            if (!$child->getData($attributeCode)) {
                throw new StateException(
                    __(
                        'The child product doesn\'t have the "%1" attribute value. Verify the value and try again.',
                        $attributeCode
                    )
                );
            }
            $attributeData[$configurableProductOption->getAttributeId()] = [
                'position' => $configurableProductOption->getPosition()
            ];
        }
        $configurableOptionData = $this->getConfigurableAttributesData($attributeData);

        /** @var \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionFactory */
        $optionFactory = $this->getOptionsFactory();
        $options = $optionFactory->create($configurableOptionData);
        $childrenIds[] = $child->getId();
        $product->getExtensionAttributes()->setConfigurableProductOptions($options);
        $product->getExtensionAttributes()->setConfigurableProductLinks($childrenIds);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * @inheritdoc
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function removeChild($sku, $childSku)
    {
        $product = $this->productRepository->get($sku);

        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            throw new InputException(
                __('The product with the "%1" SKU isn\'t a configurable product.', $sku)
            );
        }

        $options = $product->getTypeInstance()->getUsedProducts($product);
        $ids = [];
        foreach ($options as $option) {
            if ($option->getSku() == $childSku) {
                continue;
            }
            $ids[] = $option->getId();
        }
        if (count($options) == count($ids)) {
            throw new NoSuchEntityException(
                __("The option that was requested doesn't exist. Verify the entity and try again.")
            );
        }
        $product->getExtensionAttributes()->setConfigurableProductLinks($ids);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * Get Options Factory
     *
     * @return \Magento\ConfigurableProduct\Helper\Product\Options\Factory
     *
     * @deprecated 100.1.2
     */
    private function getOptionsFactory()
    {
        if (!$this->optionsFactory) {
            $this->optionsFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\ConfigurableProduct\Helper\Product\Options\Factory::class);
        }
        return $this->optionsFactory;
    }

    /**
     * Get Configurable Attribute Data
     *
     * @param int[] $attributeData
     * @return array
     */
    private function getConfigurableAttributesData($attributeData)
    {
        $configurableAttributesData = [];
        $attributeValues = [];
        $attributes = $this->attributeFactory->create()
            ->getCollection()
            ->addFieldToFilter('attribute_id', array_keys($attributeData))
            ->getItems();
        foreach ($attributes as $attribute) {
            foreach ($attribute->getOptions() as $option) {
                if ($option->getValue()) {
                    $attributeValues[] = [
                        'label' => $option->getLabel(),
                        'attribute_id' => $attribute->getId(),
                        'value_index' => $option->getValue(),
                    ];
                }
            }
            $configurableAttributesData[] =
                [
                    'attribute_id' => $attribute->getId(),
                    'code' => $attribute->getAttributeCode(),
                    'label' => $attribute->getStoreLabel(),
                    'position' => $attributeData[$attribute->getId()]['position'],
                    'values' => $attributeValues,
                ];
        }

        return $configurableAttributesData;
    }
}
