<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\OptionRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ResourceModelConfigurable;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var OptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var ResourceModelConfigurable
     */
    private $resourceModel;

    /**
     * SaveHandler constructor
     *
     * @param ResourceModelConfigurable $resourceModel
     * @param OptionRepositoryInterface $optionRepository
     */
    public function __construct(
        ResourceModelConfigurable $resourceModel,
        OptionRepositoryInterface $optionRepository
    ) {
        $this->resourceModel = $resourceModel;
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param ProductInterface $entity
     * @param array $arguments
     * @return ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getTypeId() !== Configurable::TYPE_CODE) {
            return $entity;
        }

        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $entity;
        }

        if ($extensionAttributes->getConfigurableProductOptions() !== null) {
            $this->deleteConfigurableProductAttributes($entity);
        }

        $configurableOptions = (array) $extensionAttributes->getConfigurableProductOptions();
        if (!empty($configurableOptions)) {
            $this->saveConfigurableProductAttributes($entity, $configurableOptions);
        }

        $configurableLinks = $extensionAttributes->getConfigurableProductLinks();
        if ($configurableLinks !== null) {
            $configurableLinks = (array)$configurableLinks;
            $this->resourceModel->saveProducts($entity, $configurableLinks);
        }

        return $entity;
    }

    /**
     * Save only newly created attributes for configurable product.
     *
     * @param ProductInterface $product
     * @param array $attributes
     * @return array
     */
    private function saveConfigurableProductAttributes(ProductInterface $product, array $attributes): array
    {
        $ids = [];
        $existingAttributeIds = [];
        foreach ($this->optionRepository->getList($product->getSku()) as $option) {
            $existingAttributeIds[$option->getAttributeId()] = $option;
        }
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
        foreach ($attributes as $attribute) {
            if (!in_array($attribute->getAttributeId(), array_keys($existingAttributeIds))
                || $this->isOptionChanged($existingAttributeIds[$attribute->getAttributeId()], $attribute)
            ) {
                $attribute->setId(null);
                $ids[] = $this->optionRepository->save($product->getSku(), $attribute);
            }
        }

        return $ids;
    }

    /**
     * Remove product attributes which no longer used.
     *
     * @param ProductInterface $product
     * @return void
     */
    private function deleteConfigurableProductAttributes(ProductInterface $product): void
    {
        $newAttributeIds = [];
        foreach ($product->getExtensionAttributes()->getConfigurableProductOptions() as $option) {
            $newAttributeIds[$option->getAttributeId()] = $option;
        }
        foreach ($this->optionRepository->getList($product->getSku()) as $option) {
            if (!in_array($option->getAttributeId(), array_keys($newAttributeIds))
                || $this->isOptionChanged($option, $newAttributeIds[$option->getAttributeId()])
            ) {
                $this->optionRepository->deleteById($product->getSku(), $option->getId());
            }
        }
    }

    /**
     * Check if existing option is changed.
     *
     * @param OptionInterface $option
     * @param Attribute $attribute
     * @return bool
     */
    private function isOptionChanged(OptionInterface $option, Attribute $attribute): bool
    {
        if ($option->getLabel() == $attribute->getLabel() && $option->getPosition() == $attribute->getPosition()) {
            return false;
        }

        return true;
    }
}
