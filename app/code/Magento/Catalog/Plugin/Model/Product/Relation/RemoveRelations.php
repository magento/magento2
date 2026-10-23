<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Model\Product\Relation;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Psr\Log\LoggerInterface;

/**
 * Plugin for removing entries from catalog_product_relation after product is deleted
 */
class RemoveRelations
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Delete related product relations
     *
     * @param ProductResource $subject
     * @param ProductResource $result
     * @param ProductInterface $product
     * @return ProductResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function afterDelete(
        ProductResource $subject,
        ProductResource $result,
        ProductInterface $product
    ): ProductResource {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $connection = $this->resourceConnection->getConnection();
        try {
            $connection->beginTransaction();
            $relationTable = $this->resourceConnection->getTableName('catalog_product_relation');
            $whereCondition = $connection->quoteInto('child_id = ?', $product->getId())
                . " OR "
                . $connection->quoteInto('parent_id = ?', $product->getData($linkField));
            $connection->delete($relationTable, $whereCondition);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error(\sprintf(
                'Could not delete product relations for product %1$s. %2$s',
                $product->getId(),
                $e
            ));
        }
        return $result;
    }
}
