<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Validator\Attribute;

class BackendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityMock;

    protected function setUp(): void
    {
        $this->model = new \Magento\Eav\Model\Validator\Attribute\Backend();
        $this->entityMock = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
    }

    /**
     */
    public function testisValidIfProvidedModelIsIncorrect()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Model must be extended from \\Magento\\Framework\\Model\\AbstractModel');

        $this->model->isValid(
            $this->createMock(\Magento\Framework\DataObject::class)
        );
    }

    /**
     */
    public function testisValidIfProvidedResourceModelIsIncorrect()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Model resource must be extended from \\Magento\\Eav\\Model\\Entity\\AbstractEntity');

        $resourceMock = $this->createMock(\Magento\Framework\DataObject::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);
        $this->model->isValid($this->entityMock);
    }

    public function testisValidIfAttributeValueNotValid()
    {
        $exceptionMessage = __('The value of attribute not valid');
        $attributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $resourceMock = $this->createMock(\Magento\Eav\Model\Entity\AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $resourceMock->expects($this->once())->method('loadAllAttributes')->with($this->entityMock)->willReturnSelf();
        $resourceMock->expects($this->once())->method('getAttributesByCode')->willReturn([$attributeMock]);

        $backendMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class);
        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);

        $backendMock->expects($this->once())
            ->method('validate')
            ->with($this->entityMock)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException($exceptionMessage));

        $this->assertFalse($this->model->isValid($this->entityMock));
    }

    public function testisValidIfValidationResultIsFalse()
    {
        $attributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $resourceMock = $this->createMock(\Magento\Eav\Model\Entity\AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $resourceMock->expects($this->once())->method('loadAllAttributes')->with($this->entityMock)->willReturnSelf();
        $resourceMock->expects($this->once())->method('getAttributesByCode')->willReturn([$attributeMock]);

        $backendMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class);
        $backendMock->expects($this->once())->method('validate')->with($this->entityMock)->willReturn(false);

        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);
        $attributeMock->expects($this->exactly(2))->method('getAttributeCode')->willReturn('attribute_code');

        $this->assertFalse($this->model->isValid($this->entityMock));
    }

    public function testisValidIfValidationResultIsString()
    {
        $attributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $resourceMock = $this->createMock(\Magento\Eav\Model\Entity\AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $resourceMock->expects($this->once())->method('loadAllAttributes')->with($this->entityMock)->willReturnSelf();
        $resourceMock->expects($this->once())->method('getAttributesByCode')->willReturn([$attributeMock]);

        $backendMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class);
        $backendMock->expects($this->once())->method('validate')->with($this->entityMock)->willReturn('string');

        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attribute_code');

        $this->assertFalse($this->model->isValid($this->entityMock));
    }

    public function testisValidSuccess()
    {
        $attributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $resourceMock = $this->createMock(\Magento\Eav\Model\Entity\AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getResource')->willReturn($resourceMock);

        $resourceMock->expects($this->once())->method('loadAllAttributes')->with($this->entityMock)->willReturnSelf();
        $resourceMock->expects($this->once())->method('getAttributesByCode')->willReturn([$attributeMock]);

        $backendMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class);
        $backendMock->expects($this->once())->method('validate')->with($this->entityMock)->willReturn(true);
        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);

        $this->assertTrue($this->model->isValid($this->entityMock));
    }
}
