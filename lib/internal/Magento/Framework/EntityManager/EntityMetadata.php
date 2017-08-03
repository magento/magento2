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
 * @since 2.1.0
 */
class EntityMetadata implements EntityMetadataInterface
{
    /**
     * @var ResourceConnection
     * @since 2.1.0
     */
    protected $resourceConnection;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $entityTableName;

    /**
     * @var null|string
     * @since 2.1.0
     */
    protected $connectionName;

    /**
     * @var SequenceInterface
     * @since 2.1.0
     */
    protected $sequence;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $eavEntityType;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $identifierField;

    /**
     * @var string[]
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        $entityTableName,
        $identifierField,
        SequenceInterface $sequence = null,
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
     * @since 2.1.0
     */
    public function getIdentifierField()
    {
        return $this->identifierField;
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getLinkField()
    {
        $connection = $this->resourceConnection->getConnectionByName($this->getEntityConnectionName());
        $indexList = $connection->getIndexList($this->getEntityTable());
        return $indexList[$connection->getPrimaryKeyName($this->getEntityTable())]['COLUMNS_LIST'][0];
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    public function getEntityConnection()
    {
        return $this->resourceConnection->getConnectionByName($this->connectionName);
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getEntityTable()
    {
        return $this->resourceConnection->getTableName($this->entityTableName);
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getEntityConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * @return null|string
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getEntityContext()
    {
        return $this->entityContext;
    }

    /**
     * @return null|string
     * @since 2.1.0
     */
    public function getEavEntityType()
    {
        return $this->eavEntityType;
    }
}
