<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sequence\SequenceInterface;

/**
 * Class EntityMetadata
 */
class EntityMetadata implements EntityMetadataInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var string
     */
    protected $entityTableName;

    /**
     * @var null|string
     */
    protected $connectionName;

    /**
     * @var SequenceInterface
     */
    protected $sequence;

    /**
     * @var string
     */
    protected $eavEntityType;

    /**
     * @var string
     */
    protected $identifierField;

    /**
     * @var string[]
     */
    protected $entityContext;

    /**
     * EntityMetadata constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param string $entityTableName
     * @param string $identifierField
     * @param SequenceInterface|null $sequence
     * @param null $eavEntityType
     * @param null $connectionName
     * @param array $entityContext
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        $entityTableName,
        $identifierField,
        ?SequenceInterface $sequence = null,
        $eavEntityType = null,
        $connectionName = null,
        $entityContext = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->entityTableName = $entityTableName;
        $this->eavEntityType = $eavEntityType;
        $this->connectionName = $connectionName;
        $this->identifierField = $identifierField;
        $this->sequence = $sequence;
        $this->entityContext = $entityContext;
    }

    /**
     * @return string
     */
    public function getIdentifierField()
    {
        return $this->identifierField;
    }

    /**
     * @return string
     */
    public function getLinkField()
    {
        $connection = $this->resourceConnection->getConnectionByName($this->getEntityConnectionName());
        $indexList = $connection->getIndexList($this->getEntityTable());
        return $indexList[$connection->getPrimaryKeyName($this->getEntityTable())]['COLUMNS_LIST'][0];
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @deprecated 100.1.0
     */
    public function getEntityConnection()
    {
        return $this->resourceConnection->getConnectionByName($this->connectionName);
    }

    /**
     * @return string
     */
    public function getEntityTable()
    {
        return $this->resourceConnection->getTableName($this->entityTableName);
    }

    /**
     * @return string
     */
    public function getEntityConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * @return null|string
     */
    public function generateIdentifier()
    {
        $nextIdentifier = null;
        if ($this->sequence) {
            $nextIdentifier = $this->sequence->getNextValue();
        }
        return $nextIdentifier;
    }

    /**
     * @return string[]
     */
    public function getEntityContext()
    {
        return $this->entityContext;
    }

    /**
     * @return null|string
     */
    public function getEavEntityType()
    {
        return $this->eavEntityType;
    }
}
