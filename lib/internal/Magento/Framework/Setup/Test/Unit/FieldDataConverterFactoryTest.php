<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\FieldDataConverterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\FieldDataConverter;
use Magento\Framework\Setup\DataConverter\DataConverterInterface;

class FieldDataConverterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var DataConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataConverterMock;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMock(ObjectManagerInterface::class);
        $this->connectionMock = $this->getMock(AdapterInterface::class);
        $this->dataConverterMock = $this->getMock(DataConverterInterface::class);
        $this->fieldDataConverterFactory = $objectManager->getObject(
            FieldDataConverterFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreate()
    {
        $dataConverterClassName = 'ClassName';
        $fieldDataConverterInstance = 'field data converter instance';
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($dataConverterClassName)
            ->willReturn($this->dataConverterMock);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                FieldDataConverter::class,
                [
                    'connection' => $this->connectionMock,
                    'dataConverter' => $this->dataConverterMock
                ]
            )
            ->willReturn($fieldDataConverterInstance);
        $this->assertEquals(
            $fieldDataConverterInstance,
            $this->fieldDataConverterFactory->create($this->connectionMock, $dataConverterClassName)
        );
    }
}
