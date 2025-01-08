<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SpecialPriceBulkResolver implements SpecialPriceBulkResolverInterface, ArgumentInterface
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var MetadataPool
     */
    private MetadataPool $metadataPool;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $customerSession;

    /**
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     * @param SessionManagerInterface $customerSession
     */
    public function __construct(
        ResourceConnection $resource,
        MetadataPool $metadataPool,
        SessionManagerInterface $customerSession
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->customerSession = $customerSession;
    }

    /**
     * Determines if blocks have special prices
     *
     * @param int $storeId
     * @param AbstractCollection|null $productCollection
     * @return array
     * @throws \Exception
     */
    public function generateSpecialPriceMap(int $storeId, ?AbstractCollection $productCollection): array
    {
        if (!$productCollection) {
            return [];
        }

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['e' => $this->resource->getTableName('catalog_product_entity')]
            )
            ->joinLeft(
                ['link' => $this->resource->getTableName('catalog_product_super_link')],
                'link.parent_id = e.' . $metadata->getLinkField()
            )
            ->joinLeft(
                ['product_website' => $this->resource->getTableName('catalog_product_website')],
                'product_website.product_id = link.product_id'
            )
            ->joinLeft(
                ['price' => $this->resource->getTableName('catalog_product_index_price')],
                'price.entity_id = COALESCE(link.product_id, e.entity_id) AND price.website_id = ' . $storeId .
                ' AND price.customer_group_id = ' . $this->customerSession->getCustomerGroupId()
            )
            ->where('e.entity_id IN (' . implode(',', $productCollection->getAllIds()) . ')')
            ->columns(
                [
                    'link.product_id',
                    '(price.final_price < price.price) AS hasSpecialPrice',
                    'e.' . $metadata->getLinkField() . ' AS identifier',
                    'e.entity_id'
                ]
            );
        $data = $connection->fetchAll($select);
        $map = [];
        foreach ($data as $specialPriceInfo) {
            if (!isset($map[$specialPriceInfo['entity_id']])) {
                $map[$specialPriceInfo['entity_id']] = (bool) $specialPriceInfo['hasSpecialPrice'];
            } else {
                if ($specialPriceInfo['hasSpecialPrice'] > $map[$specialPriceInfo['entity_id']]) {
                    $map[$specialPriceInfo['entity_id']] = true;
                }
            }

        }

        return $map;
    }
}
