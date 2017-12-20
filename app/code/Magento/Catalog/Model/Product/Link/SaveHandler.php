<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Link;

use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class SaveProductLinks
 */
class SaveHandler
{
    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Link
     */
    private $linkResource;

    /**
     * @var linkTypeProvider
     */
    private $linkTypeProvider;

    /**
     * SaveHandler constructor.
     * @param MetadataPool $metadataPool
     * @param Link $linkResource
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
     */
    public function __construct(
        MetadataPool $metadataPool,
        Link $linkResource,
        ProductLinkRepositoryInterface $productLinkRepository,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
    ) {
        $this->metadataPool = $metadataPool;
        $this->linkResource = $linkResource;
        $this->productLinkRepository = $productLinkRepository;
        $this->linkTypeProvider = $linkTypeProvider;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        $link = $entity->getData($this->metadataPool->getMetadata($entityType)->getLinkField());
        if ($this->linkResource->hasProductLinks($link)) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
            foreach ($this->productLinkRepository->getList($entity) as $link) {
                $this->productLinkRepository->delete($link);
            }
        }
        $productLinks = $entity->getProductLinks();

        // Build links per type
        $linksByType = [];
        foreach ($productLinks as $link) {
            $linksByType[$link->getLinkType()][] = $link;
        }

        // Do check
        $hasPositionLinkType = $this->isPositionSet($linksByType);

        // Set array position as a fallback position if necessary
        foreach ($hasPositionLinkType as $linkType => $hasPosition) {
            if (!$hasPosition) {
                array_walk($linksByType[$linkType], function ($productLink, $position) {
                    $productLink->setPosition(++$position);
                });
            }
        }

        // Flatten multi-dimensional linksByType in ProductLinks
        $productLinks = array_reduce($linksByType, 'array_merge', []);

        if (count($productLinks) > 0) {
            foreach ($entity->getProductLinks() as $link) {
                $this->productLinkRepository->save($link);
            }
        }
        return $entity;
    }

    /**
     * Check if the position is set for all product links per link type.
     * array with boolean per type
     *
     * @param $linksByType
     * @return array
     */
    private function isPositionSet($linksByType)
    {
        $linkTypes = $this->linkTypeProvider->getLinkTypes();

        // Initialize isPositionSet for existent link types
        $isPositionSet = [];
        foreach (array_keys($linkTypes) as $typeName) {
            if (array_key_exists($typeName, $linksByType)) {
                $isPositionSet[$typeName] = count($linksByType[$typeName]) > 0;
            }
        }

        // Check if at least one link without position exists per Link type
        foreach ($linksByType as $type => $links) {
            foreach ($links as $link) {
                if (!array_key_exists('position', $link->getData())) {
                    $isPositionSet[$type] = false;
                    break;
                }
            }
        }

        return $isPositionSet;
    }
}
