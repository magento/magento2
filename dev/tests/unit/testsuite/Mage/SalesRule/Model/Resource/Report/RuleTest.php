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
 * @package     Mage_SalesRule
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_SalesRule_Model_Resource_Report_RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test table name
     */
    const TABLE_NAME = 'test';

    /**
     * List of test rules;
     *
     * @var array
     */
    protected $_rules = array(
        array('rule_name' => 'test1'),
        array('rule_name' => 'test2'),
        array('rule_name' => 'test3')
    );

    public function testGetUniqRulesNamesList()
    {
        $dbAdapterMock = $this->getMockForAbstractClass('Zend_Db_Adapter_Abstract', array(), '', false);
        $select = $this->getMock('Varien_Db_Select', array('from'), array($dbAdapterMock));
        $select->expects($this->once())
            ->method('from')
            ->with(self::TABLE_NAME, $this->isInstanceOf('Zend_Db_Expr'))
            ->will($this->returnValue($select));

        $adapterMock = $this->getMock('Varien_Db_Adapter_Pdo_Mysql', array('select', 'fetchAll'), array(), '', false);
        $adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($select));
        $adapterMock->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->will($this->returnCallback(array($this, 'fetchAllCallback')));

        $resourceMock = $this->getMock('Mage_Core_Model_Resource', array('getConnection', 'getTableName'), array(), '',
            false
        );
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($adapterMock));
        $resourceMock->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue(self::TABLE_NAME));

        $model = new Mage_SalesRule_Model_Resource_Report_Rule($resourceMock);

        $expectedRuleNames = array();
        foreach ($this->_rules as $rule) {
            $expectedRuleNames[] = $rule['rule_name'];
        }
        $this->assertEquals($expectedRuleNames, $model->getUniqRulesNamesList());
    }

    /**
     * Check structure of sql query
     *
     * @param Varien_Db_Select $select
     * @return array
     */
    public function fetchAllCallback(Varien_Db_Select $select)
    {
        $whereParts = $select->getPart(Varien_Db_Select::WHERE);
        $this->assertCount(2, $whereParts);
        $this->assertContains("rule_name IS NOT NULL", $whereParts[0]);
        $this->assertContains("rule_name <> ''", $whereParts[1]);

        $orderParts = $select->getPart(Varien_Db_Select::ORDER);
        $this->assertCount(1, $orderParts);
        $expectedOrderParts = array('rule_name', 'ASC');
        $this->assertEquals($expectedOrderParts, $orderParts[0]);

        return $this->_rules;
    }
}
