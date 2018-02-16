<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory|null $attributeFactory
     * @param \Magento\ConfigurableProduct\Helper\Product\Options\Factory|null $optionsFactory
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory = null,
        \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionsFactory = null
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->configurableType = $configurableType;
        $this->dataObjectHelper = $dataObjectHelper;
        if (null === $attributeFactory) {
            $attributeFactory = ObjectManager::getInstance()->get(
                \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class
            );
        }
        $this->attributeFactory = $attributeFactory;
        if (null === $optionsFactory) {
            $optionsFactory = ObjectManager::getInstance()->get(
                \Magento\ConfigurableProduct\Helper\Product\Options\Factory::class
            );
        }
        $this->optionsFactory = $optionsFactory;
    }

    /**
     * {@inheritdoc}
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
                if (null !== $value && $attrCode != 'entity_id') {
                    $attributes[$attrCode] = $value;
                }
            }
            $attributes['store_id'] = $child->getStoreId();
            /** @var \Magento\Catalog\Api\Data\ProductInterface $productDataObject */
            $productDataObject = $this->productFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productDataObject,
                $attributes,
                '\Magento\Catalog\Api\Data\ProductInterface'
            );
            $childrenList[] = $productDataObject;
        }
        return $childrenList;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($sku, $childSku)
    {
        $product = $this->productRepository->get($sku);
        $child = $this->productRepository->get($childSku);

        $childrenIds = array_values($this->configurableType->getChildrenIds($product->getId())[0]);
        if (in_array($child->getId(), $childrenIds)) {
            throw new StateException(__('Product has been already attached'));
        }

        $configurableProductOptions = $product->getExtensionAttributes()->getConfigurableProductOptions();
        if (empty($configurableProductOptions)) {
            throw new StateException(__('Parent product does not have configurable product options'));
        }

        $attributeIds = [];
        foreach ($configurableProductOptions as $configurableProductOption) {
            $attributeCode = $configurableProductOption->getProductAttribute()->getAttributeCode();
            if (!$child->getData($attributeCode)) {
                throw new StateException(__('Child product does not have attribute value %1', $attributeCode));
            }
            $attributeIds[] = $configurableProductOption->getAttributeId();
        }
        $configurableOptionData = $this->getConfigurableAttributesData($attributeIds);

        $options = $this->optionsFactory->create($configurableOptionData);
        $childrenIds[] = $child->getId();
        $product->getExtensionAttributes()->setConfigurableProductOptions($options);
        $product->getExtensionAttributes()->setConfigurableProductLinks($childrenIds);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild($sku, $childSku)
    {
        $product = $this->productRepository->get($sku);

        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            throw new InputException(
                __('Product with specified sku: %1 is not a configurable product', $sku)
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
            throw new NoSuchEntityException(__('Requested option doesn\'t exist'));
        }
        $product->getExtensionAttributes()->setConfigurableProductLinks($ids);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * Get Configurable Attribute Data
     *
     * @param int[] $attributeIds
     * @return array
     */
    private function getConfigurableAttributesData($attributeIds)
    {
        $configurableAttributesData = [];
        $attributeValues = [];
        $attributes = $this->attributeFactory->create()
            ->getCollection()
            ->addFieldToFilter('attribute_id', $attributeIds)
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
                    'values' => $attributeValues,
                ];
        }

        return $configurableAttributesData;
    }
}
