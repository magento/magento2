<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\DB\DataConverter\DataConverterInterface;
use Magento\Framework\DB\FieldDataConverter;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FieldDataConverterFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var DataConverterInterface|MockObject
     */
    private $dataConverterMock;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    protected function setUp(): void
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
