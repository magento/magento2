<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute\AttributeGroupAlreadyExistsException;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\Model\AbstractModel;

/**
 * Eav Resource Entity Attribute Group
 */
class Group extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Attribute
     */
    private $attributeResource;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null,
        Attribute $attributeResource = null
    ) {
        parent::__construct($context, $connectionName);
        $this->attributeResource = $attributeResource ?: ObjectManager::getInstance()->get(Attribute::class);
    }

    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('eav_attribute_group', 'attribute_group_id');
    }

    /**
     * Checks if attribute group exists
     *
     * @param \Magento\Eav\Model\Entity\Attribute\Group $object
     * @return bool
     */
    public function itemExists($object)
    {
        $connection = $this->getConnection();
        $bind = [
            'attribute_set_id' => $object->getAttributeSetId(),
            'attribute_group_name' => $object->getAttributeGroupName(),
        ];
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'attribute_set_id = :attribute_set_id'
        )->where(
            'attribute_group_name = :attribute_group_name'
        );

        return $connection->fetchRow($select, $bind) > 0;
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$object->getSortOrder()) {
            $object->setSortOrder($this->_getMaxSortOrder($object) + 1);
        }
        return parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->getAttributes()) {
            foreach ($object->getAttributes() as $attribute) {
                /** @var $attribute \Magento\Eav\Api\Data\AttributeInterface */
                $attribute->setAttributeGroupId($object->getId());
                $this->attributeResource->saveInSetIncluding(
                    $attribute
                );
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Retrieve max sort order
     *
     * @param AbstractModel $object
     * @return int
     */
    protected function _getMaxSortOrder($object)
    {
        $connection = $this->getConnection();
        $bind = [':attribute_set_id' => $object->getAttributeSetId()];
        $select = $connection->select()->from(
            $this->getMainTable(),
            new \Zend_Db_Expr("MAX(sort_order)")
        )->where(
            'attribute_set_id = :attribute_set_id'
        );

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Set any group default if old one was removed
     *
     * @param integer $attributeSetId
     * @return $this
     */
    public function updateDefaultGroup($attributeSetId)
    {
        $connection = $this->getConnection();
        $bind = [':attribute_set_id' => $attributeSetId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            $this->getIdFieldName()
        )->where(
            'attribute_set_id = :attribute_set_id'
        )->order(
            'default_id ' . \Magento\Framework\Data\Collection::SORT_ORDER_DESC
        )->limit(
            1
        );

        $groupId = $connection->fetchOne($select, $bind);

        if ($groupId) {
            $data = ['default_id' => 1];
            $where = ['attribute_group_id =?' => $groupId];
            $connection->update($this->getMainTable(), $data, $where);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function saveNewObject(AbstractModel $object)
    {
        try {
            return parent::saveNewObject($object);
        } catch (DuplicateException $e) {
            throw new AttributeGroupAlreadyExistsException(
                __(
                    'Attribute group with same code already exist. Please rename "%1" group',
                    $object->getAttributeGroupName()
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function updateObject(AbstractModel $object)
    {
        try {
            return parent::updateObject($object);
        } catch (DuplicateException $e) {
            throw new AttributeGroupAlreadyExistsException(
                __(
                    'Attribute group with same code already exist. Please rename "%1" group',
                    $object->getAttributeGroupName()
                )
            );
        }
    }
}
