<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Resource\Entity\Attribute;

/**
 * Eav attribute set resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Set extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\GroupFactory
     */
    protected $_attrGroupFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\GroupFactory $attrGroupFactory
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\Resource\Entity\Attribute\GroupFactory $attrGroupFactory
    ) {
        parent::__construct($resource);
        $this->_attrGroupFactory = $attrGroupFactory;
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_attribute_set', 'attribute_set_id');
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getGroups()) {
            /* @var $group \Magento\Eav\Model\Entity\Attribute\Group */
            foreach ($object->getGroups() as $group) {
                $group->setAttributeSetId($object->getId());
                if ($group->itemExists() && !$group->getId()) {
                    continue;
                }
                $group->save();
            }
        }
        if ($object->getRemoveGroups()) {
            foreach ($object->getRemoveGroups() as $group) {
                /* @var $group \Magento\Eav\Model\Entity\Attribute\Group */
                $group->delete();
            }
            $this->_attrGroupFactory->create()->updateDefaultGroup($object->getId());
        }
        if ($object->getRemoveAttributes()) {
            foreach ($object->getRemoveAttributes() as $attribute) {
                /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $attribute->deleteEntity();
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Validate attribute set name
     *
     * @param \Magento\Eav\Model\Entity\Attribute\Set $object
     * @param string $attributeSetName
     * @return bool
     */
    public function validate($object, $attributeSetName)
    {

        $adapter = $this->_getReadAdapter();
        $bind = array('attribute_set_name' => trim($attributeSetName), 'entity_type_id' => $object->getEntityTypeId());
        $select = $adapter->select()->from(
            $this->getMainTable()
        )->where(
            'attribute_set_name = :attribute_set_name'
        )->where(
            'entity_type_id = :entity_type_id'
        );

        if ($object->getId()) {
            $bind['attribute_set_id'] = $object->getId();
            $select->where('attribute_set_id != :attribute_set_id');
        }

        return !$adapter->fetchOne($select, $bind) ? true : false;
    }

    /**
     * Retrieve Set info by attributes
     *
     * @param array $attributeIds
     * @param int $setId
     * @return array
     */
    public function getSetInfo(array $attributeIds, $setId = null)
    {
        $adapter = $this->_getReadAdapter();
        $setInfo = array();
        $attributeToSetInfo = array();

        if (count($attributeIds) > 0) {
            $select = $adapter->select()->from(
                array('entity' => $this->getTable('eav_entity_attribute')),
                array('attribute_id', 'attribute_set_id', 'attribute_group_id', 'sort_order')
            )->joinLeft(
                array('attribute_group' => $this->getTable('eav_attribute_group')),
                'entity.attribute_group_id = attribute_group.attribute_group_id',
                array('group_sort_order' => 'sort_order')
            )->where(
                'entity.attribute_id IN (?)',
                $attributeIds
            );
            $bind = array();
            if (is_numeric($setId)) {
                $bind[':attribute_set_id'] = $setId;
                $select->where('entity.attribute_set_id = :attribute_set_id');
            }
            $result = $adapter->fetchAll($select, $bind);

            foreach ($result as $row) {
                $data = array(
                    'group_id' => $row['attribute_group_id'],
                    'group_sort' => $row['group_sort_order'],
                    'sort' => $row['sort_order']
                );
                $attributeToSetInfo[$row['attribute_id']][$row['attribute_set_id']] = $data;
            }
        }

        foreach ($attributeIds as $atttibuteId) {
            $setInfo[$atttibuteId] = isset(
                $attributeToSetInfo[$atttibuteId]
            ) ? $attributeToSetInfo[$atttibuteId] : array();
        }

        return $setInfo;
    }

    /**
     * Retrurn default attribute group id for attribute set id
     *
     * @param int $setId
     * @return int|null
     */
    public function getDefaultGroupId($setId)
    {
        $adapter = $this->_getReadAdapter();
        $bind = array('attribute_set_id' => (int)$setId);
        $select = $adapter->select()->from(
            $this->getTable('eav_attribute_group'),
            'attribute_group_id'
        )->where(
            'attribute_set_id = :attribute_set_id'
        )->where(
            'default_id = 1'
        )->limit(
            1
        );
        return $adapter->fetchOne($select, $bind);
    }
}
