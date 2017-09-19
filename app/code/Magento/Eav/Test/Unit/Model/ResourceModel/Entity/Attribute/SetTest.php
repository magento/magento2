<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\ResourceModel\Entity\Attribute;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Set
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationProcessor;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->transactionManagerMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface::class
        );
        $this->relationProcessor = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class
        );
        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($this->transactionManagerMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessor);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->eavConfigMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->setMethods(['isCacheEnabled', 'getEntityType', 'getCache'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->createMock(Json::class);

        $attributeGroupFactoryMock = $this->createMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\GroupFactory::class
        );

        $this->model = $objectManager->getObject(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set::class,
            [
                'context' => $contextMock,
                'attrGroupFactory' => $attributeGroupFactoryMock,
                'eavConfig' => $this->eavConfigMock
            ]
        );

        $objectManager->setBackwardCompatibleProperty($this->model, 'serializer', $this->serializerMock);

        $this->typeMock = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $this->objectMock = $this->createPartialMock(\Magento\Framework\Model\AbstractModel::class, [
                'getEntityTypeId',
                'getAttributeSetId',
                'beforeDelete',
                'getId',
                'isDeleted',
                'afterDelete',
                'afterDeleteCommit',
                '__wakeup'
            ]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Default attribute set can not be deleted
     * @return void
     */
    public function testBeforeDeleteStateException()
    {
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class));

        $this->transactionManagerMock->expects($this->once())
            ->method('start')
            ->with($this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class))
            ->willReturn($this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class));

        $this->objectMock->expects($this->once())->method('getEntityTypeId')->willReturn(665);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(665)->willReturn($this->typeMock);
        $this->typeMock->expects($this->once())->method('getDefaultAttributeSetId')->willReturn(4);
        $this->objectMock->expects($this->once())->method('getAttributeSetId')->willReturn(4);

        $this->model->delete($this->objectMock);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage test exception
     * @return void
     */
    public function testBeforeDelete()
    {
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class));

        $this->transactionManagerMock->expects($this->once())
            ->method('start')
            ->with($this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class))
            ->willReturn($this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class));

        $this->objectMock->expects($this->once())->method('getEntityTypeId')->willReturn(665);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(665)->willReturn($this->typeMock);
        $this->typeMock->expects($this->once())->method('getDefaultAttributeSetId')->willReturn(4);
        $this->objectMock->expects($this->once())->method('getAttributeSetId')->willReturn(5);
        $this->relationProcessor->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('test exception'));

        $this->model->delete($this->objectMock);
    }

    /**
     * @return void
     */
    public function testGetSetInfoCacheMiss()
    {
        $serializedData = 'serialized data';
        $setElement = [
            10000 => [
                'group_id' => 10,
                'group_sort' => 100,
                'sort' => 1000
            ]
        ];
        $setData = [
            1 => $setElement,
            2 => [],
            3 => []
        ];
        $cached = [
            1 => $setElement
        ];
        $cacheMock = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save', 'getFrontend', 'remove', 'clean'])
            ->getMock();
        $cacheKey = Set::ATTRIBUTES_CACHE_ID . 1;
        $cacheMock
            ->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn(false);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($cached)
            ->willReturn($serializedData);
        $cacheMock
            ->expects($this->once())
            ->method('save')
            ->with(
                $serializedData,
                $cacheKey,
                [\Magento\Eav\Model\Cache\Type::CACHE_TAG, \Magento\Eav\Model\Entity\Attribute::CACHE_TAG]
            );

        $this->eavConfigMock->expects($this->any())->method('isCacheEnabled')->willReturn(true);
        $this->eavConfigMock->expects($this->any())->method('getCache')->willReturn($cacheMock);

        $fetchResult = [
            [
                'attribute_id' => 1,
                'attribute_group_id' => 10,
                'group_sort_order' => 100,
                'sort_order' => 1000,
                'attribute_set_id' => 10000
            ]
        ];

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'joinLeft', 'where'])
            ->getMock();
        $selectMock->expects($this->once())->method('from')->will($this->returnSelf());
        $selectMock->expects($this->once())->method('joinLeft')->will($this->returnSelf());
        $selectMock->expects($this->atLeastOnce())->method('where')->will($this->returnSelf());

        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'fetchAll'])
            ->getMock();
        $connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);
        $connectionMock->expects($this->atLeastOnce())->method('fetchAll')->willReturn($fetchResult);

        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->resourceMock->expects($this->any())->method('getTableName')->willReturn('_TABLE_');
        $this->assertEquals(
            $setData,
            $this->model->getSetInfo([1, 2, 3], 1)
        );
    }

    /**
     * @return void
     */
    public function testGetSetInfoCacheHit()
    {
        $setElement = [
            10000 => [
                'group_id' => 10,
                'group_sort' => 100,
                'sort' => 1000
            ]
        ];
        $setData = [
            1 => $setElement,
            2 => [],
            3 => []
        ];
        $cached = [
            1 => $setElement
        ];
        $serializedData = 'serialized data';
        $this->resourceMock->expects($this->never())->method('getConnection');
        $this->eavConfigMock->expects($this->any())->method('isCacheEnabled')->willReturn(true);
        $cacheMock = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save', 'getFrontend', 'remove', 'clean'])
            ->getMock();
        $cacheMock->expects($this->once())
            ->method('load')
            ->with(Set::ATTRIBUTES_CACHE_ID . 1)
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($cached);

        $this->eavConfigMock->expects($this->any())->method('getCache')->willReturn($cacheMock);

        $this->assertEquals(
            $setData,
            $this->model->getSetInfo([1, 2, 3], 1)
        );
    }
}
