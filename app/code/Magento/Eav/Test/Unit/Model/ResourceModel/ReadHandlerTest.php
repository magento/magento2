<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\ResourceModel;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var EntityMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataMock;

    /**
     * @var \Magento\Eav\Model\ResourceModel\ReadHandler
     */
    private $readHandler;

    /**
     * @var \Magento\Framework\Model\Entity\ScopeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolverMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $args = $objectManager->getConstructArguments(\Magento\Eav\Model\ResourceModel\ReadHandler::class);
        $this->metadataPoolMock = $args['metadataPool'];
        $this->metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->willReturn($this->metadataMock);
        $this->configMock = $args['config'];
        $this->scopeResolverMock = $args['scopeResolver'];
        $this->scopeResolverMock->method('getEntityContext')
            ->willReturn([]);

        $this->readHandler = $objectManager->getObject(\Magento\Eav\Model\ResourceModel\ReadHandler::class, $args);
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
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
        $backendMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class)
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
            ->method('getAttributes')
            ->willReturn([$attributeMock]);
        $this->assertEquals($expected, $this->readHandler->execute('entity_type', $entityData));
    }

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
