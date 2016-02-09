<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    protected $relationProcessorMock;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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

        $this->entityManager = $this->getMockBuilder('Magento\Framework\Model\EntityManager')
            ->setMethods(['load', 'save'])
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

        $associatedEntitiesMap = [
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
        ];

        $this->model = $objectManager->getObject(
            'Magento\SalesRule\Model\ResourceModel\Rule',
            [
                'context' => $context,
                'connectionName' => $connectionName,
                'associatedEntitiesMap' => $associatedEntitiesMap,
                'entityManager' => $this->entityManager
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
            ->with(RuleInterface::class, $abstractModel, $ruleId);
        $result = $this->model->load($abstractModel, $ruleId);
        $this->assertSame($this->model, $result);
    }

    public function testSave()
    {
        $connectionMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $resourceMock = $this->getMock(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            [
                '_construct',
                'getConnection',
                '__wakeup',
                'getIdFieldName'
            ],
            [],
            '',
            false
        );
        $connectionInterfaceMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionInterfaceMock));
        $data = 'tableName';
        $this->resourcesMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));
        $this->resourcesMock->expects($this->any())->method('getTableName')->with($data)->will(
            $this->returnValue('tableName')
        );
        $mainTableReflection = new \ReflectionProperty(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            '_mainTable'
        );
        $mainTableReflection->setAccessible(true);
        $mainTableReflection->setValue($this->model, 'tableName');
        $idFieldNameReflection = new \ReflectionProperty(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            '_idFieldName'
        );
        $idFieldNameReflection->setAccessible(true);
        $idFieldNameReflection->setValue($this->model, 'idFieldName');
        $connectionMock->expects($this->any())->method('save')->with('tableName', 'idFieldName');
        $connectionMock->expects($this->any())->method('quoteInto')->will($this->returnValue('idFieldName'));

        $abstractModelMock = $this->setupAbstractModel($resourceMock);
        $abstractModelMock->setIdFieldName('id');
        $abstractModelMock->setData(
            [
                'id'    => 12345,
                'name'  => 'Test Name',
                'value' => 'Test Value'
            ]
        );
        $abstractModelMock->afterLoad();
        $this->assertEquals($abstractModelMock->getData(), $abstractModelMock->getStoredData());
        $newData = ['value' => 'Test Value New'];
        $abstractModelMock->addData($newData);
        $this->assertNotEquals($abstractModelMock->getData(), $abstractModelMock->getStoredData());
        $abstractModelMock->isObjectNew(false);
        $connectionMock->expects($this->any())
            ->method('update')
            ->with(
                'tableName',
                $newData,
                'idFieldName'
            );
        $this->relationProcessorMock->expects($this->once())
            ->method('validateDataIntegrity');
        $this->entityManager->expects($this->once())
            ->method('save');

        $this->model->save($abstractModelMock);
    }

    private function setupAbstractModel($resourceMock)
    {
        $context = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Framework\Model\Context'
        );
        $registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $resourceCollectionMock = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $extensionFactoryMock = $this->getMockBuilder(
            '\Magento\Framework\Api\ExtensionAttributesFactory'
        )->disableOriginalConstructor()
            ->getMock();
        $customAttributeFactoryMock = $this->getMockBuilder('\Magento\Framework\Api\AttributeValueFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $formFactoryMock = $this->getMockBuilder('\Magento\Framework\Data\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $localeDateMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');

        /** @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject $abstractModelMock */
        $abstractModelMock = $this->getMockForAbstractClass(
            'Magento\Rule\Model\AbstractModel',
            [
                $context,
                $registryMock,
                $extensionFactoryMock,
                $customAttributeFactoryMock,
                $formFactoryMock,
                $localeDateMock,
                $resourceMock,
                $resourceCollectionMock
            ]
        );

        $conditionMock = $this->getMockBuilder('\Magento\Rule\Model\Condition\Combine')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'asArray',
                    'setRule',
                    'setId',
                ]
            )
            ->getMock();
        $conditionMock->expects($this->any())
            ->method('asArray')
            ->willReturn([]);
        $conditionMock->expects($this->any())
            ->method('setRule')
            ->willReturnSelf();
        $conditionMock->expects($this->any())
            ->method('setId')
            ->willReturnSelf();

        $actionMock = $this->getMockBuilder('\Magento\Rule\Model\Action\Collection')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'asArray',
                    'setRule',
                    'setId',
                ]
            )
            ->getMock();
        $actionMock->expects($this->any())
            ->method('setRule')
            ->willReturnSelf();
        $actionMock->expects($this->any())
            ->method('setId')
            ->willReturnSelf();

        $actionMock->expects($this->any())
            ->method('asArray')
            ->willReturn([]);
        $abstractModelMock->expects($this->any())
            ->method('getConditions')
            ->willReturn($conditionMock);
        $abstractModelMock->expects($this->any())
            ->method('getConditionsInstance')
            ->willReturn($conditionMock);
        $abstractModelMock->expects($this->any())
            ->method('getActions')
            ->willReturn($actionMock);
        $abstractModelMock->expects($this->any())
            ->method('getActionsInstance')
            ->willReturn($actionMock);

        return $abstractModelMock;
    }
}
