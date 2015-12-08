<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Framework\Model\Entity\MetadataPool;

/**
 * Class SaveHandler
 */
class SaveHandler
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @param OptionRepository $optionRepository
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        OptionRepository $optionRepository,
        MetadataPool $metadataPool
    ) {
        $this->optionRepository = $optionRepository;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        if ($entity->getTypeId() !== 'bundle') {
            return $entity;
        }
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->optionRepository->getList($entity->getSku()) as $option) {
            $this->optionRepository->delete($option);
        }
        $options = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];
        foreach ($options as $option) {
            $this->optionRepository->save($entity, $option);
        }
        return $entity;
    }
}
