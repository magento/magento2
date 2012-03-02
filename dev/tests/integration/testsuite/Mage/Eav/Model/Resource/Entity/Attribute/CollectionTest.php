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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Eav
 */
class Mage_Eav_Model_Resource_Entity_Attribute_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Eav_Model_Resource_Entity_Attribute_Collection
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Eav_Model_Resource_Entity_Attribute_Collection();
    }

    public function testSetAttributeSetExcludeFilter()
    {
        $collection = new Mage_Eav_Model_Resource_Entity_Attribute_Collection();
        $setsPresent = $this->_getSets($collection);
        $excludeSetId = current($setsPresent);

        $this->_model->setAttributeSetExcludeFilter($excludeSetId);
        $sets = $this->_getSets($this->_model);

        $this->assertNotContains($excludeSetId, $sets);
    }

    /**
     * Returns array of set ids, present in collection attributes
     *
     * @param Mage_Eav_Model_Resource_Entity_Attribute_Collection $collection
     * @return array
     */
    protected function _getSets($collection)
    {
        $collection->addSetInfo();

        $sets = array();
        foreach ($collection as $attribute) {
            foreach (array_keys($attribute->getAttributeSetInfo()) as $setId) {
                $sets[$setId] = $setId;
            }
        }
        return array_values($sets);
    }

    public function testSetAttributeGroupFilter()
    {
        $collection = new Mage_Eav_Model_Resource_Entity_Attribute_Collection();
        $groupsPresent = $this->_getGroups($collection);
        $includeGroupId = current($groupsPresent);

        $this->_model->setAttributeGroupFilter($includeGroupId);
        $groups = $this->_getGroups($this->_model);

        $this->assertEquals(array($includeGroupId), $groups);
    }

    /**
     * Returns array of group ids, present in collection attributes
     *
     * @param Mage_Eav_Model_Resource_Entity_Attribute_Collection $collection
     * @return array
     */
    protected function _getGroups($collection)
    {
        $collection->addSetInfo();

        $groups = array();
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
        $this->assertEmpty($select->getPart(Zend_Db_Select::GROUP));
        $this->_model->addAttributeGrouping();
        $this->assertEquals(array('main_table.attribute_id'), $select->getPart(Zend_Db_Select::GROUP));
    }
}
