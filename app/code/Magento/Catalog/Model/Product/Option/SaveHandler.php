<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
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
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->optionRepository->getProductOptions($entity) as $option) {
            $this->optionRepository->delete($option);
        }
        if ($entity->getOptions()) {
            foreach ($entity->getOptions() as $option) {
                $this->optionRepository->save($option);
            }
        }
        return $entity;
    }
}
