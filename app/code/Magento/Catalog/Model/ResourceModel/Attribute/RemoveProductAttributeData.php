<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;

/**
 * Class for deleting data from attribute additional table by attribute set id.
 */
class RemoveProductAttributeData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Deletes data from attribute table by attribute set id.
     *
     * @param AbstractModel $object
     * @param int $attributeSetId
     * @return void
     */
    public function removeData(AbstractModel $object, int $attributeSetId): void
    {
        $backendTable = $object->getBackend()->getTable();
        if ($backendTable) {
            $linkField = $this->metadataPool
                ->getMetadata(ProductInterface::class)
                ->getLinkField();

            $backendLinkField = $object->getBackend()->getEntityIdField();

            $select = $this->resourceConnection->getConnection()->select()
                ->from(['b' => $backendTable])
                ->join(
                    ['e' => $object->getEntity()->getEntityTable()],
                    "b.$backendLinkField = e.$linkField"
                )->where('b.attribute_id = ?', $object->getId())
                ->where('e.attribute_set_id = ?', $attributeSetId);

            $this->resourceConnection->getConnection()->query($select->deleteFromSelect('b'));
        }
    }
}
