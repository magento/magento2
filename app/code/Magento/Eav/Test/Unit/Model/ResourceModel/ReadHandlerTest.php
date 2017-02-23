<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\ResourceMode;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ReadHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Eav\Model\ResourceModel\ReadHandler
     */
    private $readHandler;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readHandler = (new ObjectManager($this))->getObject(
            \Magento\Eav\Model\ResourceModel\ReadHandler::class,
            [
                'metadataPool' => $this->metadataPoolMock,
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @param string $eavEntityType
     * @param int $callNum
     * @param array $expected
     * @dataProvider getAttributesDataProvider
     */
    public function testGetAttributes($eavEntityType, $callNum, array $expected)
    {
        $metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getEavEntityType')
            ->willReturn($eavEntityType);
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock->expects($this->exactly($callNum))
            ->method('getAttributes')
            ->willReturn([$attributeMock]);
        $getAttributesMethod = new \ReflectionMethod(
            \Magento\Eav\Model\ResourceModel\ReadHandler::class,
            'getAttributes'
        );
        $getAttributesMethod->setAccessible(true);
        $this->assertEquals($expected, $getAttributesMethod->invoke($this->readHandler, 'entity_type'));
    }

    public function getAttributesDataProvider()
    {
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            [null, 0, []],
            ['env-entity-type', 1, [$attributeMock]]
        ];
    }

    /**
     * @expectedException \Exception
     */
    public function testGetAttributesWithException()
    {
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->willThrowException(new \Exception('Unknown entity type'));
        $this->configMock->expects($this->never())
            ->method('getAttributes');
        $getAttributesMethod = new \ReflectionMethod(
            \Magento\Eav\Model\ResourceModel\ReadHandler::class,
            'getAttributes'
        );
        $getAttributesMethod->setAccessible(true);
        $getAttributesMethod->invoke($this->readHandler, 'entity_type');
    }
}
