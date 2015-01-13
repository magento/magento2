<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Resource\Entity\Attribute;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Collection
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Eav\Model\Resource\Entity\Attribute\Collection'
        );
    }

    /**
     * Returns array of set ids, present in collection attributes
     *
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Collection $collection
     * @return array
     */
    protected function _getSets($collection)
    {
        $collection->addSetInfo();

        $sets = [];
        foreach ($collection as $attribute) {
            foreach (array_keys($attribute->getAttributeSetInfo()) as $setId) {
                $sets[$setId] = $setId;
            }
        }
        return array_values($sets);
    }

    public function testSetAttributeGroupFilter()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Eav\Model\Resource\Entity\Attribute\Collection'
        );
        $groupsPresent = $this->_getGroups($collection);
        $includeGroupId = current($groupsPresent);

        $this->_model->setAttributeGroupFilter($includeGroupId);
        $groups = $this->_getGroups($this->_model);

        $this->assertEquals([$includeGroupId], $groups);
    }

    /**
     * Returns array of group ids, present in collection attributes
     *
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Collection $collection
     * @return array
     */
    protected function _getGroups($collection)
    {
        $collection->addSetInfo();

        $groups = [];
        foreach ($collection as $attribute) {
            foreach ($attribute->getAttributeSetInfo() as $setInfo) {
                $groupId = $setInfo['group_id'];
                $groups[$groupId] = $groupId;
            }
        }
        return array_values($groups);
    }

    public function testAddAttributeGrouping()
    {
        $select = $this->_model->getSelect();
        $this->assertEmpty($select->getPart(\Zend_Db_Select::GROUP));
        $this->_model->addAttributeGrouping();
        $this->assertEquals(['main_table.attribute_id'], $select->getPart(\Zend_Db_Select::GROUP));
    }
}
