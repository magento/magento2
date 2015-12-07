<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\ResourceModel\Product\Link;

use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Link\Action\SaveProductLinks;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Framework\Model\ResourceModel\Db\ProcessEntityRelationInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as TypeGrouped;

/**
 * Class SaveHandler
 */
class SaveHandler implements ProcessEntityRelationInterface
{
    /**
     * @var ProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductLinkManagementInterface
     */
    protected $linkManagement;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var SaveProductLinks
     */
    protected $saveProductLinks;

    /**
     * @var Relation
     */
    protected $catalogProductRelation;

    /**
     * Init
     *
     * @param MetadataPool $metadataPool
     * @param SaveProductLinks $saveProductLinks
     * @param Relation $catalogProductRelation
     * @param ProductLinkInterfaceFactory $productLinkFactory
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ProductLinkManagementInterface $linkManagement
     */
    public function __construct(
        MetadataPool $metadataPool,
        SaveProductLinks $saveProductLinks,
        Relation $catalogProductRelation,
        ProductLinkInterfaceFactory $productLinkFactory,
        ProductLinkRepositoryInterface $productLinkRepository,
        ProductRepositoryInterface $productRepository,
        ProductLinkManagementInterface $linkManagement
    ) {

        $this->metadataPool = $metadataPool;
        $this->saveProductLinks = $saveProductLinks;
        $this->catalogProductRelation = $catalogProductRelation;
        $this->productLinkFactory = $productLinkFactory;
        $this->productLinkRepository = $productLinkRepository;
        $this->productRepository = $productRepository;
        $this->linkManagement = $linkManagement;
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
        $productIds = [];
        $productSkuIds = [];
        $oldLinks = $this->productLinkRepository->getList($entity);

        foreach($entity->getProductLinks() as $link) {
            if ($link->getLinkType() == 'associated') {
                $this->productLinkRepository->save($link);
                $product = $this->productRepository->get($link->getLinkedProductSku());
                if ($product) {
                    $productIds[] = $product->getId();
                    $productSkuIds[] = $link->getLinkedProductSku();
                }
            }
        }
        foreach ($oldLinks as $link) {
            if ($link->getLinkType() == 'associated' && !in_array($link->getLinkedProductSku(), $productSkuIds)) {
                $this->productLinkRepository->delete($link);
            }
        }
        $this->catalogProductRelation->processRelations($entity->getEntityId(), $productIds);

        return $entity;
    }
}
