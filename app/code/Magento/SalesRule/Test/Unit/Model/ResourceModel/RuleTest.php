<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\ResourceModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

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
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleResource = $this->getMockBuilder(\Magento\SalesRule\Model\ResourceModel\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionName = 'test';
        $this->resourcesMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->relationProcessorMock =
            $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionManagerMock =
            $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourcesMock);

        $this->entityManager = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityManager::class)
            ->setMethods(['load', 'save', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
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

        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $associatedEntitiesMap = $this->getMock(
            \Magento\SalesRule\Model\ResourceModel\Rule\AssociatedEntityMap::class,
            ['getData'],
            [],
            '',
            false
        );
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

        $this->model = $this->objectManager->getObject(
            \Magento\SalesRule\Model\ResourceModel\Rule::class,
            [
                'context' => $context,
                'connectionName' => $connectionName,
                'entityManager' => $this->entityManager,
                'associatedEntityMapInstance' => $associatedEntitiesMap
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
        $abstractModel = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
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
}
