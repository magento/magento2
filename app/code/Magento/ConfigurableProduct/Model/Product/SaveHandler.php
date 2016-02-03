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
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Framework\Model\Entity\MetadataPool;

/**
 * Class SaveHandler
 */
class SaveHandler
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var OptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var ConfigurableFactory
     */
    private $configurableFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * SaveHandler constructor
     * @param OptionRepositoryInterface $optionRepository
     * @param MetadataPool $metadataPool
     * @param ConfigurableFactory $configurableFactory
     * @param CollectionFactory $collectionFactory
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        OptionRepositoryInterface $optionRepository,
        MetadataPool $metadataPool,
        ConfigurableFactory $configurableFactory,
        CollectionFactory $collectionFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->optionRepository = $optionRepository;
        $this->metadataPool = $metadataPool;
        $this->configurableFactory = $configurableFactory;
        $this->collectionFactory = $collectionFactory;
        $this->productAttributeRepository = $productAttributeRepository;
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

        $configurableOptions = $extensionAttributes->getConfigurableProductOptions();
        if (!empty($configurableOptions)) {
            $ids = $this->saveConfigurableProductAttributes($entity, $configurableOptions);
            $this->deleteConfigurableProductAttributes($entity, $ids);
        }

        $configurableLinks = $extensionAttributes->getConfigurableProductLinks();
        if (!empty($configurableLinks)) {
            /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable */
            $configurable = $this->configurableFactory->create();
            $configurable->saveProducts($entity, $configurableLinks);
        }

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
            $attribute->loadByProductAndAttribute($product, $eavAttribute);
            $ids[] = $this->optionRepository->save($product->getSku(), $attribute);
        }
        return $ids;
    }

    /**
     * Remove all product attributes
     *
     * @param ProductInterface $product
     * @param array $ids
     * @return void
     */
    private function deleteConfigurableProductAttributes(ProductInterface $product, array $ids)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setProductFilter($product);
        $collection->addFieldToFilter(
            'product_super_attribute_id',
            ['nin' => $ids]
        );
        $collection->addFieldToFilter(
            'product_id',
            $product->getData($metadata->getLinkField())
        );
        $collection->walk('delete');
    }
}
