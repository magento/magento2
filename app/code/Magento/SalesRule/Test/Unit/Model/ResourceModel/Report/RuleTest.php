<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\ResourceModel\Report;

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
    protected $_rules = [
        ['rule_name' => 'test1'],
        ['rule_name' => 'test2'],
        ['rule_name' => 'test3'],
    ];

    public function testGetUniqRulesNamesList()
    {
        $dbAdapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->setMethods(['_connect', 'quote'])
            ->disableOriginalConstructor()
            ->getMock();
        $dbAdapterMock
            ->expects($this->any())
            ->method('quote')
            ->willReturnCallback(
                function ($value) {
                    return "'$value'";
                }
            );

        $selectRenderer = $this->getMockBuilder(\Magento\Framework\DB\Select\SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select = $this->getMock(\Magento\Framework\DB\Select::class, ['from'], [$dbAdapterMock, $selectRenderer]);
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

        $connectionMock = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'fetchAll'],
            [],
            '',
            false
        );
        $connectionMock->expects($this->once())->method('select')->will($this->returnValue($select));
        $connectionMock->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $select
        )->will(
            $this->returnCallback([$this, 'fetchAllCallback'])
        );

        $resourceMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $resourceMock->expects($this->once())->method('getTableName')->will($this->returnValue(self::TABLE_NAME));

        $flagFactory = $this->getMock(\Magento\Reports\Model\FlagFactory::class, [], [], '', false);
        $createdatFactoryMock = $this->getMock(
            \Magento\SalesRule\Model\ResourceModel\Report\Rule\CreatedatFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $updatedatFactoryMock = $this->getMock(
            \Magento\SalesRule\Model\ResourceModel\Report\Rule\UpdatedatFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectHelper->getObject(
            \Magento\SalesRule\Model\ResourceModel\Report\Rule::class,
            [
                'resource' => $resourceMock,
                'reportsFlagFactory' => $flagFactory,
                'createdatFactory' => $createdatFactoryMock,
                'updatedatFactory' => $updatedatFactoryMock
            ]
        );

        $expectedRuleNames = [];
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
        $expectedOrderParts = ['rule_name', 'ASC'];
        $this->assertEquals($expectedOrderParts, $orderParts[0]);

        return $this->_rules;
    }
}
