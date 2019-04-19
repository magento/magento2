<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\ResourceModel;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\ResourceModel\ReadHandler;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\Entity\ScopeResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Model\Entity\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Test for Magento\Eav\Model\ResourceModel\ReadHandler class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var EntityMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataMock;

    /**
     * @var ScopeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolverMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->scopeResolverMock = $this->createMock(ScopeResolver::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->metadataMock = $this->createMock(EntityMetadataInterface::class);
        $this->scopeMock = $this->createMock(ScopeInterface::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->attributeMock = $this->createMock(AbstractAttribute::class);

        $this->readHandler = $this->objectManager->getObject(
            ReadHandler::class,
            [
                'metadataPool' => $this->metadataPoolMock,
                'scopeResolver' => $this->scopeResolverMock,
                'logger' => $this->loggerMock,
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $eavEntityType = 'env-entity-type';
        $entityData = ['linkField' => 'theLinkField'];
        $attributeId = '1';
        $attributeCode = 'attribute_code';
        $identifier = 'store_id';
        $expectedEntityData = [
            'linkField' => 'theLinkField',
            $attributeCode => $attributeId,
        ];
        $entityDataObject = $this->objectManager->getObject(DataObject::class, ['data' => $entityData]);
        $abstractBackendMock = $this->createMock(AbstractBackend::class);
        $selectMock = $this->createMock(Select::class);
        $orderedUnionSelectMock = $this->createMock(Select::class);
        $fallbackScopeMock = $this->createMock(ScopeInterface::class);

        $this->metadataPoolMock->expects($this->exactly(2))->method('getMetadata')->willReturn($this->metadataMock);
        $this->metadataMock->expects($this->exactly(2))->method('getEavEntityType')->willReturn($eavEntityType);
        $this->scopeResolverMock->expects($this->once())
            ->method('getEntityContext')
            ->with($eavEntityType, $entityData)
            ->willReturn([$this->scopeMock]);
        $this->metadataMock->expects($this->once())->method('getEntityConnection')->willReturn($this->connectionMock);
        $this->configMock->expects($this->once())
            ->method('getEntityAttributes')
            ->with($eavEntityType, $entityDataObject)
            ->willReturn([$this->attributeMock]);
        $this->attributeMock->expects($this->once())->method('isStatic')->willReturn(false);
        $this->attributeMock->expects($this->once())->method('getBackend')->willReturn($abstractBackendMock);
        $abstractBackendMock->expects($this->once())->method('getTable')->willReturn('some_table');
        $this->attributeMock->expects($this->exactly(2))->method('getAttributeId')->willReturn($attributeId);
        $this->attributeMock->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $this->connectionMock->expects($this->at(0))->method('select')->willReturn($selectMock);
        $selectMock->expects($this->at(0))
            ->method('from')
            ->with(
                [
                    't' => 'some_table',
                ],
                [
                    'value' => 't.value',
                    'attribute_id' => 't.attribute_id',
                ]
            )->willReturnSelf();
        $this->metadataMock->expects($this->exactly(2))->method('getLinkField')->willReturn('linkField');
        $selectMock->expects($this->at(1))
            ->method('where')
            ->with('linkField = ?', 'theLinkField')
            ->willReturnSelf();
        $selectMock->expects($this->at(2))
            ->method('where')
            ->with('attribute_id IN (?)', [$attributeId])
            ->willReturnSelf();
        $this->scopeMock->expects($this->exactly(2))->method('getIdentifier')->willReturn($identifier);
        $this->connectionMock->expects($this->at(1))
            ->method('quoteIdentifier')
            ->with($identifier)
            ->willReturn("`$identifier`");
        $selectMock->expects($this->at(3))
            ->method('where')
            ->with(
                "`$identifier` IN (?)",
                [
                    0 => '1',
                    1 => 0,
                ]
            )->willReturnSelf();
        $this->scopeMock->expects($this->once())->method('getValue')->willReturn('1');
        $this->scopeMock->expects($this->exactly(2))->method('getFallback')->willReturn($fallbackScopeMock);
        $fallbackScopeMock->expects($this->once())->method('getValue')->willReturn(0);
        $fallbackScopeMock->expects($this->once())->method('getFallback')->willReturn(null);
        $selectMock->expects($this->at(4))->method('columns')->with($identifier, 't')->willReturnSelf();
        $this->connectionMock->expects($this->at(2))->method('select')->willReturn($orderedUnionSelectMock);

        $unionSelect = $this->objectManager->getObject(
            UnionExpression::class,
            [
                'parts' => [$selectMock],
                'type' => 'UNION ALL',
                'pattern' => '( %s )',
            ]
        );
        $orderedUnionSelectMock->expects($this->once())->method('from')->with(['u' => $unionSelect])->willReturnSelf();
        $orderedUnionSelectMock->expects($this->once())->method('order')->with($identifier)->willReturnSelf();
        $this->connectionMock->expects($this->at(3))
            ->method('fetchAll')
            ->with($orderedUnionSelectMock)
            ->willReturn([
                [
                    'attribute_id' => '1',
                    'value' => '1',
                    'store_id' => '1',
                ]
            ]);

        $this->assertEquals($expectedEntityData, $this->readHandler->execute($eavEntityType, $entityData));
    }

    /**
     * @return void
     */
    public function testExecuteWithStaticAttributeAttributes()
    {
        $eavEntityType = 'env-entity-type';
        $entityData = ['linkField' => 'theLinkField'];
        $entityDataObject = $this->objectManager->getObject(DataObject::class, ['data' => $entityData]);
        $this->attributeMock = $this->createMock(AbstractAttribute::class);

        $this->metadataPoolMock->expects($this->exactly(2))
            ->method('getMetadata')
            ->willReturn($this->metadataMock);
        $this->metadataMock->expects($this->exactly(2))->method('getEavEntityType')->willReturn($eavEntityType);
        $this->scopeResolverMock->expects($this->once())
            ->method('getEntityContext')
            ->with($eavEntityType, $entityData)
            ->willReturn([$this->scopeMock]);
        $this->metadataMock->expects($this->once())->method('getEntityConnection')->willReturn($this->connectionMock);
        $this->configMock->expects($this->once())
            ->method('getEntityAttributes')
            ->with($eavEntityType, $entityDataObject)
            ->willReturn([$this->attributeMock]);
        $this->attributeMock->expects($this->once())->method('isStatic')->willReturn(true);

        $this->assertEquals($entityData, $this->readHandler->execute($eavEntityType, $entityData));
    }

    /**
     * @return void
     */
    public function testExecuteWithoutAttribute()
    {
        $entityData = ['linkField' => 'theLinkField'];

        $this->metadataPoolMock->expects($this->once())->method('getMetadata')->willReturn($this->metadataMock);
        $this->metadataMock->expects($this->once())->method('getEavEntityType')->willReturn(null);

        $this->assertEquals($entityData, $this->readHandler->execute('env-entity-type', $entityData));
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteWithException()
    {
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->willThrowException(new \Exception('Unknown entity type'));
        $this->configMock->expects($this->never())
            ->method('getAttributes');
        $this->readHandler->execute('entity_type', ['linkField' => 'theLinkField']);
    }
}
