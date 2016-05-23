<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\OptionRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ResourceModelConfigurable;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

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
            $attribute->setId(null);
            $ids[] = $this->optionRepository->save($product->getSku(), $attribute);
        }

        return $ids;
    }

    /**
     * Remove product attributes
     *
     * @param ProductInterface $product
     * @return void
     */
    private function deleteConfigurableProductAttributes(ProductInterface $product)
    {
        $list = $this->optionRepository->getList($product->getSku());
        foreach ($list as $item) {
            $this->optionRepository->deleteById($product->getSku(), $item->getId());
        }
    }
}
