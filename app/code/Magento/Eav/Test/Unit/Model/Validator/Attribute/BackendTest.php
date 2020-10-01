<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Validator\Attribute;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackendTest extends TestCase
{
    /**
     * @var Backend
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $entityMock;

    protected function setUp(): void
    {
        $this->model = new Backend();
        $this->entityMock = $this->createMock(AbstractModel::class);
    }

    public function testisValidIfProvidedModelIsIncorrect()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Model must be extended from \Magento\Framework\Model\AbstractModel');
        $this->model->isValid(
            $this->createMock(DataObject::class)
        );
    }

    public function testisValidIfProvidedResourceModelIsIncorrect()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Model resource must be extended from \Magento\Eav\Model\Entity\AbstractEntity');
        $resourceMock = $this->createMock(DataObject::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);
        $this->model->isValid($this->entityMock);
    }

    public function testisValidIfAttributeValueNotValid()
    {
        $exceptionMessage = __('The value of attribute not valid');
        $attributeMock = $this->createMock(Attribute::class);
        $resourceMock = $this->createMock(AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $resourceMock->expects($this->once())->method('loadAllAttributes')->with($this->entityMock)->willReturnSelf();
        $resourceMock->expects($this->once())->method('getAttributesByCode')->willReturn([$attributeMock]);

        $backendMock = $this->createMock(AbstractBackend::class);
        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);

        $backendMock->expects($this->once())
            ->method('validate')
            ->with($this->entityMock)
            ->willThrowException(new LocalizedException($exceptionMessage));

        $this->assertFalse($this->model->isValid($this->entityMock));
    }

    public function testisValidIfValidationResultIsFalse()
    {
        $attributeMock = $this->createMock(Attribute::class);
        $resourceMock = $this->createMock(AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $resourceMock->expects($this->once())->method('loadAllAttributes')->with($this->entityMock)->willReturnSelf();
        $resourceMock->expects($this->once())->method('getAttributesByCode')->willReturn([$attributeMock]);

        $backendMock = $this->createMock(AbstractBackend::class);
        $backendMock->expects($this->once())->method('validate')->with($this->entityMock)->willReturn(false);

        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);
        $attributeMock->expects($this->exactly(2))->method('getAttributeCode')->willReturn('attribute_code');

        $this->assertFalse($this->model->isValid($this->entityMock));
    }

    public function testisValidIfValidationResultIsString()
    {
        $attributeMock = $this->createMock(Attribute::class);
        $resourceMock = $this->createMock(AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $resourceMock->expects($this->once())->method('loadAllAttributes')->with($this->entityMock)->willReturnSelf();
        $resourceMock->expects($this->once())->method('getAttributesByCode')->willReturn([$attributeMock]);

        $backendMock = $this->createMock(AbstractBackend::class);
        $backendMock->expects($this->once())->method('validate')->with($this->entityMock)->willReturn('string');

        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attribute_code');

        $this->assertFalse($this->model->isValid($this->entityMock));
    }

    public function testisValidSuccess()
    {
        $attributeMock = $this->createMock(Attribute::class);
        $resourceMock = $this->createMock(AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $resourceMock->expects($this->once())->method('loadAllAttributes')->with($this->entityMock)->willReturnSelf();
        $resourceMock->expects($this->once())->method('getAttributesByCode')->willReturn([$attributeMock]);

        $backendMock = $this->createMock(AbstractBackend::class);
        $backendMock->expects($this->once())->method('validate')->with($this->entityMock)->willReturn(true);
        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);

        $this->assertTrue($this->model->isValid($this->entityMock));
    }
}
