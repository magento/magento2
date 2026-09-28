<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\CustomOptions;

use Magento\Catalog\Api\Data\CustomOptionExtensionInterface;
use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor as FileProcessor;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomOptionTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var CustomOption
     */
    protected $model;

    /** @var ExtensionAttributesFactory|MockObject */
    private $extensionAttributesFactoryMock;

    /** @var CustomOptionExtensionInterface|MockObject */
    private $extensionMock;

    /**
     * @var FileProcessor|MockObject
     */
    protected $fileProcessor;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $this->extensionAttributesFactoryMock = $this->createMock(ExtensionAttributesFactory::class);
        $attributeValueFactory = $this->createMock(AttributeValueFactory::class);
        $this->fileProcessor = $this->createMock(FileProcessor::class);
        $resource = $this->createMock(AbstractResource::class);
        $collection = $this->createMock(AbstractDb::class);

        $this->extensionMock = $this->createPartialMockWithReflection(
            CustomOptionExtensionInterface::class,
            ['setFileInfo', 'getFileInfo']
        );
        $fileInfo = null;
        $this->extensionMock->method('setFileInfo')->willReturnCallback(function ($value) use (&$fileInfo) {
            $fileInfo = $value;
            return $this->extensionMock;
        });
        $this->extensionMock->method('getFileInfo')->willReturnCallback(function () use (&$fileInfo) {
            return $fileInfo;
        });

        $this->extensionAttributesFactoryMock->expects(self::any())
            ->method('create')->willReturn($this->extensionMock);

        $this->model = new CustomOption(
            $context,
            $registry,
            $this->extensionAttributesFactoryMock,
            $attributeValueFactory,
            $this->fileProcessor,
            $resource,
            $collection
        );
    }

    public function testGetSetOptionId()
    {
        $this->assertNull($this->model->getOptionId());

        $this->model->setOptionId(1);
        $this->assertEquals(1, $this->model->getOptionId());
    }

    public function testGetOptionValue()
    {
        $this->assertNull($this->model->getOptionValue());

        $this->model->setData(CustomOptionInterface::OPTION_VALUE, 'test');
        $this->assertEquals('test', $this->model->getOptionValue());

        $this->model->setData(CustomOptionInterface::OPTION_VALUE, 'file');
        $this->assertEquals('file', $this->model->getOptionValue());
    }

    public function testGetOptionValueWithFileInfo()
    {
        $imageContent = $this->createMock(ImageContentInterface::class);

        $this->extensionMock->setFileInfo($imageContent);

        $imageResult = [
            'type' => 'type',
            'title' => 'title',
            'fullpath' => 'fullpath',
            'quote_path' => 'quote_path',
            'order_path' => 'order_path',
            'size' => 100,
            'width' => 100,
            'height' => 100,
            'secret_key' => 'secret_key',
        ];

        $this->fileProcessor->expects($this->once())
            ->method('processFileContent')
            ->with($imageContent)
            ->willReturn($imageResult);

        $this->model->setData(CustomOptionInterface::OPTION_VALUE, 'file');
        $this->assertEquals($imageResult, $this->model->getOptionValue());
    }

    public function testSetOptionValue()
    {
        $this->model->setOptionValue('test');
        $this->assertEquals('test', $this->model->getOptionValue());
    }
}
