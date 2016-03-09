<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\Model\Entity\MetadataPool;

/**
 * Class ReadHandler
 */

class ReadHandler
{
    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @param ProductCustomOptionRepositoryInterface $optionRepository
     * @param MetadataPool $metadataPool
     * @param OptionFactory $optionFactory
     */
    public function __construct(
        ProductCustomOptionRepositoryInterface $optionRepository,
        MetadataPool $metadataPool,
        OptionFactory $optionFactory
    ) {
        $this->optionRepository = $optionRepository;
        $this->metadataPool = $metadataPool;
        $this->optionFactory = $optionFactory;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        $options = [];
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        foreach ($this->optionRepository->getProductOptions($entity) as $option) {
            $option->setProduct($entity);
            $options[] = $option;
        }
        $entity->setOptions($options);
        return $entity;
    }
}
