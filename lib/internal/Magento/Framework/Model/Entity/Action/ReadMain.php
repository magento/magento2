<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity\Action;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\ReadEntityRow;

/**
 * Class ReadMain
 */
class ReadMain
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ReadEntityRow
     */
    protected $readEntityRow;

    /**
     * @param MetadataPool $metadataPool
     * @param ReadEntityRow $readEntityRow
     */
    public function __construct(
        MetadataPool $metadataPool,
        ReadEntityRow $readEntityRow
    ) {
        $this->metadataPool = $metadataPool;
        $this->readEntityRow = $readEntityRow;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param string $identifier
     * @return object
     */
    public function execute($entityType, $entity, $identifier)
    {
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $data = $this->readEntityRow->execute($entityType, $identifier);
        return $hydrator->hydrate($entity, $data);
    }
}
