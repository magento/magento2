<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Model\Entity\MetadataPool;

/**
 * Class ReadHandler
 */
class ReadHandler
{
    /**
     * @var OptionValueInterfaceFactory
     */
    private $optionValueFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * ReadHandler constructor
     * @param OptionValueInterfaceFactory $optionValueFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(OptionValueInterfaceFactory $optionValueFactory, MetadataPool $metadataPool)
    {
        $this->optionValueFactory = $optionValueFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param string $entityType
     * @param ProductInterface $entity
     * @return ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        if ($entity->getTypeId() !== Configurable::TYPE_CODE) {
            return $entity;
        }

        $extensionAttributes = $entity->getExtensionAttributes();
        $extensionAttributes->setConfigurableProductOptions($this->getOptions($entity));
        $extensionAttributes->setConfigurableProductLinks($this->getLinkedProducts($entity));
        $entity->setExtensionAttributes($extensionAttributes);
        return $entity;
    }

    /**
     * Get configurable options
     *
     * @param ProductInterface $product
     * @return OptionInterface[]
     */
    private function getOptions(ProductInterface $product)
    {
        $options = [];
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $attributeCollection = $typeInstance->getConfigurableAttributes($product);

        foreach ($attributeCollection as $attribute) {
            $values = [];
            $attributeOptions = $attribute->getOptions();
            if (is_array($attributeOptions)) {
                foreach ($attributeOptions as $option) {
                    /** @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterface $value */
                    $value = $this->optionValueFactory->create();
                    $value->setValueIndex($option['value_index']);
                    $values[] = $value;
                }
            }
            $attribute->setValues($values);
            $options[] = $attribute;
        }
        return $options;
    }

    /**
     * Get linked to configurable simple products
     *
     * @param ProductInterface $product
     * @return int[]
     */
    private function getLinkedProducts(ProductInterface $product)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $childrenIds = $typeInstance->getChildrenIds($product->getData($metadata->getLinkField()));

        if (isset($childrenIds[0])) {
            return $childrenIds[0];
        } else {
            return [];
        }
    }
}
