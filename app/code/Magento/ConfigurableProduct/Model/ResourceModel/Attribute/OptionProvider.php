<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Attribute;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider
 *
 * @since 2.2.0
 */
class OptionProvider
{
    /**
     * Product metadata pool
     *
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @since 2.2.0
     */
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * Get product entity link field
     *
     * @return string
     * @since 2.2.0
     */
    public function getProductEntityLinkField()
    {
        return $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }
}
