<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @param ProductCustomOptionRepositoryInterface $optionRepository
     */
    public function __construct(
        ProductCustomOptionRepositoryInterface $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
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
