<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\ResourceModel\Rule;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Rule
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $ruleResource;

    /**
     * @var MockObject
     */
    protected $entityManager;

    /**
     * @var MockObject|AdapterInterface
     */
    protected $adapter;

    /**
     * @var MockObject
     */
    protected $select;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourcesMock;

    /**
     * @var MockObject
     */
    protected $transactionManagerMock;

    /**
     * @var MockObject
     */
    protected $rule;

    /**
     * @var MockObject
     */
    protected $relationProcessorMock;

    /**
     * @var MockObject
     */
    private $metadataPoolMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleResource = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionName = 'test';
        $this->resourcesMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->relationProcessorMock =
            $this->getMockBuilder(ObjectRelationProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->transactionManagerMock =
            $this->getMockBuilder(TransactionManagerInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();

        $context->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourcesMock);

        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['load', 'save', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $associatedEntitiesMap = $this->createPartialMock(DataObject::class, ['getData']);
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
        $serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            Rule::class,
            [
                'context' => $context,
                'connectionName' => $connectionName,
                'entityManager' => $this->entityManager,
                'associatedEntityMapInstance' => $associatedEntitiesMap,
                'serializer' => $serializerMock,
                'metadataPool' => $this->metadataPoolMock
            ]
        );
    }

    /**
     * test load
     */
    public function testLoad()
    {
        $ruleId = 1;
        /** @var AbstractModel|MockObject $abstractModel */
        $abstractModel = $this->getMockBuilder(AbstractModel::class)
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
     * Check that can parse JSON string correctly.
     *
     * @param string $testString
     * @param array $expects
     * @dataProvider dataProviderForProductAttributes
     */
    public function testGetProductAttributes($testString, $expects)
    {
        $result = $this->model->getProductAttributes($testString);
        $this->assertEquals($expects, $result);
    }

    /**
     * Checks that linked field is used for rule labels
     */
    public function testSaveStoreLabels()
    {
        $entityMetadataInterfaceMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $entityMetadataInterfaceMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('fieldName');
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->willReturn($entityMetadataInterfaceMock);
        $this->model->saveStoreLabels(1, ['test']);
    }

    /**
     * @return array
     */
    public function dataProviderForProductAttributes()
    {
        return [
            [
                json_encode([
                    'type' => Product::class,
                    'attribute' => 'some_attribute',
                ]),
                [
                    'some_attribute',
                ]
            ],
            [
                json_encode([
                    [
                        'type' => Product::class,
                        'attribute' => 'some_attribute',
                    ],
                    [
                        'type' => Product::class,
                        'attribute' => 'some_attribute2',
                    ],
                ]),
                [
                    'some_attribute',
                    'some_attribute2',
                ]
            ],
            [
                json_encode([
                    'type' => Found::class,
                    'attribute' => 'some_attribute',
                ]),
                []
            ],
            [
                json_encode([]),
                []
            ],
        ];
    }
}
