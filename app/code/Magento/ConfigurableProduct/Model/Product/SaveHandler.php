<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Api\OptionRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ResourceModelConfigurable;

/**
 * Class SaveHandler
 */
class SaveHandler
{
    /**
     * @var OptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var ResourceModelConfigurable
     */
    private $resourceModel;

    /**
     * SaveHandler constructor
     *
     * @param ResourceModelConfigurable $resourceModel
     * @param OptionRepositoryInterface $optionRepository
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        ResourceModelConfigurable $resourceModel,
        OptionRepositoryInterface $optionRepository,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->optionRepository = $optionRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param string $entityType
     * @param ProductInterface $entity
     * @return ProductInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, ProductInterface $entity)
    {
        if ($entity->getTypeId() !== Configurable::TYPE_CODE) {
            return $entity;
        }

        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $entity;
        }

        $ids = [];
        $configurableOptions = (array) $extensionAttributes->getConfigurableProductOptions();
        if (!empty($configurableOptions)) {
            $ids = $this->saveConfigurableProductAttributes($entity, $configurableOptions);
        }

        $this->deleteConfigurableProductAttributes($entity, $ids);

        $configurableLinks = (array) $extensionAttributes->getConfigurableProductLinks();
        $this->resourceModel->saveProducts($entity, $configurableLinks);

        return $entity;
    }

    /**
     * Save attributes for configurable product
     *
     * @param ProductInterface $product
     * @param array $attributes
     * @return array
     */
    private function saveConfigurableProductAttributes(ProductInterface $product, array $attributes)
    {
        $ids = [];
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
        foreach ($attributes as $attribute) {
            $eavAttribute = $this->productAttributeRepository->get($attribute->getAttributeId());

            $data = $attribute->getData();
            $attribute->loadByProductAndAttribute($product, $eavAttribute);
            $attribute->setData(array_replace_recursive($attribute->getData(), $data));

            $ids[] = $this->optionRepository->save($product->getSku(), $attribute);
        }

        return $ids;
    }

    /**
     * Remove product attributes
     *
     * @param ProductInterface $product
     * @param array $attributesIds
     * @return void
     */
    private function deleteConfigurableProductAttributes(ProductInterface $product, array $attributesIds)
    {
        $list = $this->optionRepository->getList($product->getSku());
        foreach ($list as $item) {
            if (!in_array($item->getId(), $attributesIds)) {
                $this->optionRepository->deleteById($product->getSku(), $item->getId());
            }
        }
    }
}
