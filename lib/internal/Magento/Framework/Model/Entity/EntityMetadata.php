<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Sequence\SequenceInterface;

/**
 * Class EntityMetadata
 */
class EntityMetadata
{

    /**
     * @var AppResource
     */
    protected $appResource;

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
     * @var array
     */
    protected $fields;

    /**
     * @param AppResource $appResource
     * @param string $entityTableName
     * @param string $identifierField
     * @param SequenceInterface|null $sequence
     * @param string|null $eavEntityType
     * @param string|null $connectionName
     * @param array $entityContext
     * @param array $fields
     */
    public function __construct(
        AppResource $appResource,
        $entityTableName,
        $identifierField,
        SequenceInterface $sequence = null,
        $eavEntityType = null,
        $connectionName = null,
        $entityContext = [],
        $fields = []
    ) {
        $this->appResource = $appResource;
        $this->entityTableName = $entityTableName;
        $this->eavEntityType = $eavEntityType;
        $this->connectionName = $connectionName;
        $this->identifierField = $identifierField;
        $this->sequence = $sequence;
        $this->entityContext = $entityContext;
        $this->fields = $fields;
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
        $indexList = $this->getEntityConnection()->getIndexList($this->getEntityTable());
        return $indexList[$this->getEntityConnection()->getPrimaryKeyName($this->getEntityTable())]['COLUMNS_LIST'][0];
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getEntityConnection()
    {
        return $this->appResource->getConnectionByName($this->connectionName);
    }

    /**
     * @return string
     */
    public function getEntityTable()
    {
        return $this->appResource->getTableName($this->entityTableName);
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

    /**
     * @return array
     */
    public function getExtensionFields()
    {
        return $this->fields;
    }

    /**
     * Check is entity exists
     *
     * @param string $identifier
     * @return bool
     */
    public function checkIsEntityExists($identifier)
    {
        return (bool)$this->getEntityConnection()->fetchOne(
            $this->getEntityConnection()
                ->select()
                ->from($this->getEntityTable(), [$this->getIdentifierField()])
                ->where($this->getIdentifierField() . ' = ?', $identifier)
                ->limit(1)
        );
    }
}
