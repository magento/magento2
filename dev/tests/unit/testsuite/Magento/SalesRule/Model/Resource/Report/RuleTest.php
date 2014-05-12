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
namespace Magento\SalesRule\Model\Resource\Report;

class RuleTest extends \PHPUnit_Framework_TestCase
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
        $select = $this->getMock('Magento\Framework\DB\Select', array('from'), array($dbAdapterMock));
        $select->expects(
            $this->once()
        )->method(
            'from'
        )->with(
            self::TABLE_NAME,
            $this->isInstanceOf('Zend_Db_Expr')
        )->will(
            $this->returnValue($select)
        );

        $adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('select', 'fetchAll'),
            array(),
            '',
            false
        );
        $adapterMock->expects($this->once())->method('select')->will($this->returnValue($select));
        $adapterMock->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $select
        )->will(
            $this->returnCallback(array($this, 'fetchAllCallback'))
        );

        $resourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            array('getConnection', 'getTableName'),
            array(),
            '',
            false
        );
        $resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($adapterMock));
        $resourceMock->expects($this->once())->method('getTableName')->will($this->returnValue(self::TABLE_NAME));

        $flagFactory = $this->getMock('Magento\Reports\Model\FlagFactory', array(), array(), '', false);
        $createdatFactoryMock = $this->getMock(
            'Magento\SalesRule\Model\Resource\Report\Rule\CreatedatFactory',
            array('create'),
            array(),
            '',
            false
        );
        $updatedatFactoryMock = $this->getMock(
            'Magento\SalesRule\Model\Resource\Report\Rule\UpdatedatFactory',
            array('create'),
            array(),
            '',
            false
        );

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $model = $objectHelper->getObject(
            'Magento\SalesRule\Model\Resource\Report\Rule',
            array(
                'resource' => $resourceMock,
                'reportsFlagFactory' => $flagFactory,
                'createdatFactory' => $createdatFactoryMock,
                'updatedatFactory' => $updatedatFactoryMock
            )
        );

        $expectedRuleNames = array();
        foreach ($this->_rules as $rule) {
            $expectedRuleNames[] = $rule['rule_name'];
        }
        $this->assertEquals($expectedRuleNames, $model->getUniqRulesNamesList());
    }

    /**
     * Check structure of sql query
     *
     * @param \Magento\Framework\DB\Select $select
     * @return array
     */
    public function fetchAllCallback(\Magento\Framework\DB\Select $select)
    {
        $whereParts = $select->getPart(\Magento\Framework\DB\Select::WHERE);
        $this->assertCount(2, $whereParts);
        $this->assertContains("rule_name IS NOT NULL", $whereParts[0]);
        $this->assertContains("rule_name <> ''", $whereParts[1]);

        $orderParts = $select->getPart(\Magento\Framework\DB\Select::ORDER);
        $this->assertCount(1, $orderParts);
        $expectedOrderParts = array('rule_name', 'ASC');
        $this->assertEquals($expectedOrderParts, $orderParts[0]);

        return $this->_rules;
    }
}
