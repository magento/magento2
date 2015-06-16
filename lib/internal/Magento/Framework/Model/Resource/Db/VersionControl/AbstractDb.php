<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Resource\Db\VersionControl;

/**
 * Class AbstractDb with snapshot saving and relation save processing
 */
abstract class AbstractDb extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var Snapshot
     */
    protected $entitySnapshot;

    /**
     * @var RelationComposite
     */
    protected $entityRelationComposite;

    /**
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param string $resourcePrefix
     */
    public function __construct(
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        \Magento\Framework\Model\Resource\Db\Context $context,
        $resourcePrefix = null
    ) {
        $this->entitySnapshot = $entitySnapshot;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->entitySnapshot->registerSnapshot($object);
        return parent::_afterLoad($object);
    }

    /**
     * @inheritdoc
     */
    protected function updateObject(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . '=?', $object->getId());
        $data = $this->_prepareDataForSave($object);
        unset($data[$this->getIdFieldName()]);
        $this->_getWriteAdapter()->update($this->getMainTable(), $data, $condition);
    }

    /**
     * @inheritdoc
     */
    protected function saveNewObject(\Magento\Framework\Model\AbstractModel $object)
    {
        $bind = $this->_prepareDataForSave($object);
        unset($bind[$this->getIdFieldName()]);
        $this->_getWriteAdapter()->insert($this->getMainTable(), $bind);
        $object->setId($this->_getWriteAdapter()->lastInsertId($this->getMainTable()));
        if ($this->_useIsObjectNew) {
            $object->isObjectNew(false);
        }
    }

    /**
     * @inheritdoc
     */
    protected function processAfterSaves(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_afterSave($object);
        $this->entitySnapshot->registerSnapshot($object);
        $object->afterSave();
        $this->entityRelationComposite->processRelations($object);
    }

    protected function isModified(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this->entitySnapshot->isModified($object);
    }
}