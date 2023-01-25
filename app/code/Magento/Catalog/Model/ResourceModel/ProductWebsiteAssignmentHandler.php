<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Framework\EntityManager\Operation\AttributeInterface;

/**
 * Class purpose is to handle product websites assignment
 */
class ProductWebsiteAssignmentHandler implements AttributeInterface
{
    /**
     * @var Link
     */
    private $productLink;

    /**
     * ProductWebsiteAssignmentHandler constructor
     *
     * @param Link $productLink
     */
    public function __construct(
        Link $productLink
    ) {
        $this->productLink = $productLink;
    }

    /**
     * Assign product website entity to the product repository
     *
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function execute($entityType, $entityData, $arguments = []): array
    {
        $websiteIds = array_key_exists('website_ids', $entityData) ?
            array_filter($entityData['website_ids'], function ($websiteId) {
                return $websiteId !== null;
            }) : [];
        $productId = array_key_exists('entity_id', $entityData) ? (int) $entityData['entity_id'] : null;

        if (!empty($productId) && !empty($websiteIds)) {
            $this->productLink->updateProductWebsite($productId, $websiteIds);
        }
        return $entityData;
    }
}
