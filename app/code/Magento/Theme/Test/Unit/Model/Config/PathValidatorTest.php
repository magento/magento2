<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Config;

use Magento\Framework\DataObject;
use Magento\Theme\Api\Data\DesignConfigDataInterface;
use Magento\Theme\Api\Data\DesignConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Theme\Model\Config\PathValidator;
use Magento\Config\Model\Config\Structure;
use Magento\Theme\Model\DesignConfigRepository;
use Magento\Framework\Exception\ValidatorException;

class PathValidatorTest extends TestCase
{
    /**
     * @var Structure|MockObject
     */
    private $structure;

    /**
     * @var DesignConfigRepository|MockObject
     */
    private $designConfigRepository;

    /**
     * @var PathValidator
     */
    private $pathValidator;

    protected function setUp(): void
    {
        $this->structure = $this->createMock(Structure::class);
        $this->designConfigRepository = $this->createMock(DesignConfigRepository::class);

        $this->pathValidator = new PathValidator(
            $this->structure,
            $this->designConfigRepository
        );
    }

    public function testValidateNonDesignPath()
    {
        $path = 'non_design/path';
        $this->structure->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($path)
            ->willReturn(null);

        $this->structure->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn(['non_design/path' => 'non_design/path']);

        $result = $this->pathValidator->validate($path);
        $this->assertTrue($result);
    }

    public function testValidateDesignPath()
    {
        $path = 'design/path';
        $element = $this->createMock(Structure\Element\Field::class);
        $designConfig = $this->createMock(DesignConfigInterface::class);
        $extensionAttributes = $this->createMock(DataObject::class);
        $designConfigData = $this->createMock(DesignConfigDataInterface::class);

        $element->expects($this->exactly(2))
            ->method('getConfigPath')
            ->willReturn($path);
        $this->structure->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($path)
            ->willReturn($element);
        $this->structure->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn([]);
        $this->designConfigRepository->expects($this->once())
            ->method('getByScope')
            ->with('default', null)
            ->willReturn($designConfig);
        $designConfig->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('getDesignConfigData')
            )->willReturn([$designConfigData]);
        $designConfigData->expects($this->exactly(2))
            ->method('getFieldConfig')
            ->willReturn(['path' => $path]);

        $result = $this->pathValidator->validate($path);
        $this->assertTrue($result);
    }

    public function testValidateDesignPathThrowsException()
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('The "design/invalid_path" path doesn\'t exist. Verify and try again.');

        $path = 'design/invalid_path';
        $element = $this->createMock(Structure\Element\Field::class);
        $designConfig = $this->createMock(DesignConfigInterface::class);
        $extensionAttributes = $this->createMock(DataObject::class);

        $element->expects($this->exactly(2))
            ->method('getConfigPath')
            ->willReturn($path);
        $this->structure->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($path)
            ->willReturn($element);
        $this->structure->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn([]);
        $this->designConfigRepository->expects($this->once())
            ->method('getByScope')
            ->with('default', null)
            ->willReturn($designConfig);
        $designConfig->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('getDesignConfigData')
            )->willReturn([]);

        $this->pathValidator->validate($path);
    }
}
