<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\GetProductTypeByIdInterface;

/**
 * @inheritdoc
 */
class GetProductTypeById implements GetProductTypeByIdInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $productId)
    {
        $connection = $this->resource->getConnection();
        $productTable = $this->resource->getTableName('catalog_product_entity');

        $select = $connection->select()
            ->from(
                $productTable,
                ProductInterface::TYPE_ID
            )->where('entity_id = ?', $productId);

        $result = $connection->fetchOne($select);
        return $result ?: '';
    }
}
