<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\ResourceModel;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\ResourceModel\ReadHandler;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\Entity\ScopeResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var EntityMetadataInterface|MockObject
     */
    private $metadataMock;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var ScopeResolver|MockObject
     */
    private $scopeResolverMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $args = $objectManager->getConstructArguments(ReadHandler::class);
        $this->metadataPoolMock = $args['metadataPool'];
        $this->metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->willReturn($this->metadataMock);
        $this->configMock = $args['config'];
        $this->scopeResolverMock = $args['scopeResolver'];
        $this->scopeResolverMock->method('getEntityContext')
            ->willReturn([]);

        $this->readHandler = $objectManager->getObject(ReadHandler::class, $args);
    }

    /**
     * @param string $eavEntityType
     * @param int $callNum
     * @param array $expected
     * @param bool $isStatic
     * @dataProvider executeDataProvider
     */
    public function testExecute($eavEntityType, $callNum, array $expected, $isStatic = true)
    {
        $entityData = ['linkField' => 'theLinkField'];
        $this->metadataMock->method('getEavEntityType')
            ->willReturn($eavEntityType);
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->method('from')
            ->willReturnSelf();
        $selectMock->method('where')
            ->willReturnSelf();
        $connectionMock->method('select')
            ->willReturn($selectMock);
        $connectionMock->method('fetchAll')
            ->willReturn(
                [
                    [
                        'attribute_id' => 'attributeId',
                        'value' => 'attributeValue',
                    ]
                ]
            );
        $this->metadataMock->method('getEntityConnection')
            ->willReturn($connectionMock);
        $this->metadataMock->method('getLinkField')
            ->willReturn('linkField');

        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->method('isStatic')
            ->willReturn($isStatic);
        $backendMock = $this->getMockBuilder(AbstractBackend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendMock->method('getTable')
            ->willReturn('backendTable');
        $attributeMock->method('getBackend')
            ->willReturn($backendMock);
        $attributeMock->method('getAttributeId')
            ->willReturn('attributeId');
        $attributeMock->method('getAttributeCode')
            ->willReturn('attributeCode');
        $this->configMock->expects($this->exactly($callNum))
            ->method('getEntityAttributes')
            ->willReturn([$attributeMock]);
        $this->assertEquals($expected, $this->readHandler->execute('entity_type', $entityData));
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'null entity type' => [null, 0, ['linkField' => 'theLinkField']],
            'static attribute' => ['env-entity-type', 1, ['linkField' => 'theLinkField']],
            'non-static attribute' => [
                'env-entity-type',
                1,
                [
                    'linkField' => 'theLinkField',
                    'attributeCode' => 'attributeValue'
                ],
                false
            ],
        ];
    }

    public function testExecuteWithException()
    {
        $this->expectException('Exception');
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->willThrowException(new \Exception('Unknown entity type'));
        $this->configMock->expects($this->never())
            ->method('getAttributes');
        $this->readHandler->execute('entity_type', ['linkField' => 'theLinkField']);
    }
}
