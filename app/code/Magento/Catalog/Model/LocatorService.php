<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

class LocatorService
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * LocatorService constructor.
     *
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @return string
     */
    public function getProductLinkField() : string
    {
        return $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
    }

    /**
     * @param array $compareArray
     * @param int   $limit
     *
     * @return array
     */
    public function truncateToLimit(array $compareArray, int $limit) : array
    {
        if (count($compareArray) > $limit) {
            $compareArray = array_slice($compareArray, round($limit / -2));
        }

        return $compareArray;
    }

    /**
     * @param string $sku
     *
     * @return string
     */
    public function skuProcess(string  $sku) : string
    {
        return strtolower(trim($sku));
    }
}

