<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel;

use Magento\SalesRule\Api\Data\RuleInterface;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourcesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rule;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationProcessorMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleResource = $this->getMockBuilder('Magento\SalesRule\Model\ResourceModel\Rule')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionName = 'test';
        $this->resourcesMock = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->relationProcessorMock =
            $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionManagerMock =
            $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourcesMock);

        $this->entityManager = $this->getMockBuilder('Magento\Framework\EntityManager\EntityManager')
            ->setMethods(['load', 'save', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourcesMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);
        $this->resourcesMock->expects($this->any())
            ->method('getTableName')
            ->withAnyParameters()
            ->willReturnArgument(0);

        $context->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessorMock);
        $context->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($this->transactionManagerMock);

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $associatedEntitiesMap = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $associatedEntitiesMap->expects($this->once())
            ->method('getData')
            ->willReturn(
                [
                    'customer_group' => [
                        'associations_table' => 'salesrule_customer_group',
                        'rule_id_field' => 'rule_id',
                        'entity_id_field' => 'customer_group_id'
                    ],
                    'website' => [
                        'associations_table' => 'salesrule_website',
                        'rule_id_field' => 'rule_id',
                        'entity_id_field' => 'website_id'
                    ],
                ]
            );

        $this->prepareObjectManager([
            [
                'Magento\SalesRule\Model\ResourceModel\Rule\AssociatedEntityMap',
                $associatedEntitiesMap
            ],
        ]);

        $this->model = $objectManager->getObject(
            'Magento\SalesRule\Model\ResourceModel\Rule',
            [
                'context' => $context,
                'connectionName' => $connectionName,
                'entityManager' => $this->entityManager,
            ]
        );
    }

    /**
     * test load
     */
    public function testLoad()
    {
        $ruleId = 1;
        /** @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject $abstractModel */
        $abstractModel = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->entityManager->expects($this->once())
            ->method('load')
            ->with($abstractModel, $ruleId);
        $result = $this->model->load($abstractModel, $ruleId);
        $this->assertSame($this->model, $result);
    }

    public function testSave()
    {
        $this->entityManager->expects($this->once())
            ->method('save')
            ->with($this->rule);
        $this->assertEquals($this->model->save($this->rule), $this->model);
    }

    public function testDelete()
    {
        $this->entityManager->expects($this->once())
            ->method('delete')
            ->with($this->rule);
        $this->assertEquals($this->model->delete($this->rule), $this->model);
    }

    /**
     * @param $map
     */
    private function prepareObjectManager($map)
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())->method('getInstance')->willReturnSelf();
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($map));
        $reflectionClass = new \ReflectionClass('Magento\Framework\App\ObjectManager');
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectManagerMock);
    }
}
