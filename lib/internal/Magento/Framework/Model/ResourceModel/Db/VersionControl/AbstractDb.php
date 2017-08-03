<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db\VersionControl;

/**
 * Class AbstractDb with snapshot saving and relation save processing
 * @since 2.0.0
 */
abstract class AbstractDb extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Snapshot
     * @since 2.0.0
     */
    protected $entitySnapshot;

    /**
     * @var RelationComposite
     * @since 2.0.0
     */
    protected $entityRelationComposite;

    /**
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        $connectionName = null
    ) {
        $this->entitySnapshot = $entitySnapshot;
        $this->entityRelationComposite = $entityRelationComposite;
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->entitySnapshot->registerSnapshot($object);
        return parent::_afterLoad($object);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function processAfterSaves(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_afterSave($object);
        $this->entitySnapshot->registerSnapshot($object);
        $object->afterSave();
        $this->entityRelationComposite->processRelations($object);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function isModified(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this->entitySnapshot->isModified($object);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function processNotModifiedSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->entityRelationComposite->processRelations($object);
        return $this;
    }
}
