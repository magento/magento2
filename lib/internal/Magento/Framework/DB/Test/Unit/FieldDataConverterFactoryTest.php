<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\DataConverter\DataConverterInterface;

class FieldDataConverterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

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
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->dataConverterMock = $this->createMock(DataConverterInterface::class);
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
                    'dataConverter' => $this->dataConverterMock
                ]
            )
            ->willReturn($fieldDataConverterInstance);
        $this->assertEquals(
            $fieldDataConverterInstance,
            $this->fieldDataConverterFactory->create($dataConverterClassName)
        );
    }
}
