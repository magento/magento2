<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\ResourceModel\Product\Link;

use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Framework\Model\ResourceModel\Db\ProcessEntityRelationInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as TypeGrouped;

/**
 * Class DeleteHandler
 */
class DeleteHandler implements ProcessEntityRelationInterface
{
    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Relation
     */
    protected $catalogProductRelation;

    /**
     * Init
     *
     * @param MetadataPool $metadataPool
     * @param Relation $catalogProductRelation
     * @param ProductLinkRepositoryInterface $productLinkRepository
     */
    public function __construct(
        MetadataPool $metadataPool,
        Relation $catalogProductRelation,
        ProductLinkRepositoryInterface $productLinkRepository
    ) {

        $this->metadataPool = $metadataPool;
        $this->catalogProductRelation = $catalogProductRelation;
        $this->productLinkRepository = $productLinkRepository;
    }

    /**
     * Run operation
     *
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($entityType, $entity)
    {
        /**
         * @var $entity \Magento\Catalog\Api\Data\ProductInterface
         */
        if ($entity->getTypeId() != TypeGrouped::TYPE_CODE) {
            return $entity;
        }

        foreach ($entity->getProductLinks() as $link) {
            if ($link->getLinkType() == 'associated') {
                $this->productLinkRepository->delete($link);
            }
        }
        $this->catalogProductRelation->processRelations($entity->getEntityId(), []);

        return $entity;
    }
}
