<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource;

use Magento\Framework\Model\Resource\Db\AbstractDb;
use Magento\Sales\Model\EntityInterface;
use Magento\SalesSequence\Model\Manager;

/**
 * Abstract sales entity provides to its children knowledge about eventPrefix and eventObject
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class EntityAbstract extends AbstractDb
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'resource';

    /**
     * Use additional is object new check for this resource
     *
     * @var bool
     */
    protected $_useIsObjectNew = true;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $_eavEntityTypeFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Attribute
     */
    protected $attribute;


    /**
     * @var Manager
     */
    protected $sequenceManager;

    /**
     * @var EntitySnapshot
     */
    protected $entitySnapshot;

    /**
     * @var EntityRelationComposite
     */
    protected $entityRelationComposite;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param Attribute $attribute
     * @param Manager $sequenceManager
     * @param EntitySnapshot $entitySnapshot
     * @param EntityRelationComposite $entityRelationComposite
     * @param string $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Sales\Model\Resource\Attribute $attribute,
        Manager $sequenceManager,
        EntitySnapshot $entitySnapshot,
        EntityRelationComposite $entityRelationComposite,
        $resourcePrefix = null
    ) {
        $this->attribute = $attribute;
        $this->sequenceManager = $sequenceManager;
        $this->entitySnapshot = $entitySnapshot;
        $this->entityRelationComposite = $entityRelationComposite;
        if ($resourcePrefix === null) {
            $resourcePrefix = 'sales';
        }
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $attribute
     * @return $this
     * @throws \Exception
     */
    public function saveAttribute(\Magento\Framework\Model\AbstractModel $object, $attribute)
    {
        $this->attribute->saveAttribute($object, $attribute);
        return $this;
    }

    /**
     * Prepares data for saving and removes update time (if exists).
     * This prevents saving same update time on each entity update.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    protected function _prepareDataForSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $data = parent::_prepareDataForTable($object, $this->getMainTable());

        if (isset($data['updated_at'])) {
            unset($data['updated_at']);
        }

        return $data;
    }

    /**
     * Perform actions before object save
     * Perform actions before object save, calculate next sequence value for increment Id
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\AbstractModel $object */
        if ($object instanceof EntityInterface && $object->getIncrementId() == null) {
            $object->setIncrementId(
                $this->sequenceManager->getSequence(
                    $object->getEntityType(),
                    $object->getStore()->getGroup()->getDefaultStoreId()
                )->getNextValue()
            );
        }
        parent::_beforeSave($object);
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $adapter = $this->_getReadAdapter();
        $columns = $adapter->describeTable($this->getMainTable());

        if (isset($columns['created_at'], $columns['updated_at'])) {
            $select = $adapter->select()
                ->from($this->getMainTable(), ['created_at', 'updated_at'])
                ->where($this->getIdFieldName() . ' = :entity_id');
            $row = $adapter->fetchRow($select, [':entity_id' => $object->getId()]);

            if (is_array($row) && isset($row['created_at'], $row['updated_at'])) {
                $object->setCreatedAt($row['created_at']);
                $object->setUpdatedAt($row['updated_at']);
            }
        }

        parent::_afterSave($object);
        return $this;
    }

    /**
     * Perform actions after object delete
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);
        return $this;
    }

    /**
     * Perform actions after object load, mark loaded data as data without changes
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->entitySnapshot->registerSnapshot($object);
        return $this;
    }

    /**
     * Save entity
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }
        if (!$this->entitySnapshot->isModified($object)) {
            $this->entityRelationComposite->processRelations($object);
            return $this;
        }
        $this->beginTransaction();

        try {
            $object->validateBeforeSave();
            $object->beforeSave();
            if ($object->isSaveAllowed()) {
                $this->_serializeFields($object);
                $this->_beforeSave($object);
                $this->_checkUnique($object);
                $this->objectRelationProcessor->validateDataIntegrity($this->getMainTable(), $object->getData());
                if ($object->getId() !== null && (!$this->_useIsObjectNew || !$object->isObjectNew())) {
                    $condition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . '=?', $object->getId());
                    $data = $this->_prepareDataForSave($object);
                    unset($data[$this->getIdFieldName()]);
                    $this->_getWriteAdapter()->update($this->getMainTable(), $data, $condition);
                } else {
                    $bind = $this->_prepareDataForSave($object);
                    unset($bind[$this->getIdFieldName()]);
                    $this->_getWriteAdapter()->insert($this->getMainTable(), $bind);
                    $object->setId($this->_getWriteAdapter()->lastInsertId($this->getMainTable()));
                    if ($this->_useIsObjectNew) {
                        $object->isObjectNew(false);
                    }
                }
                $this->unserializeFields($object);
                $this->_afterSave($object);
                $this->entitySnapshot->registerSnapshot($object);
                $object->afterSave();
                $this->entityRelationComposite->processRelations($object);
            }
            $this->addCommitCallback([$object, 'afterCommitCallback'])->commit();
            $object->setHasDataChanges(false);
        } catch (\Exception $e) {
            $this->rollBack();
            $object->setHasDataChanges(true);
            throw $e;
        }
        return $this;
    }
}
