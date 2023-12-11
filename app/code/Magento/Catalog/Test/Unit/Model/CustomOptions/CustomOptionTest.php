<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomOptionTest extends TestCase
{
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
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesFactoryMock = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributeValueFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessor = $this->getMockBuilder(
            \Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $resource = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collection = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionMock = $this->getMockBuilder(CustomOptionExtensionInterface::class)
            ->setMethods(['getFileInfo'])
            ->getMockForAbstractClass();

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
        $imageContent = $this->getMockBuilder(ImageContentInterface::class)
            ->getMockForAbstractClass();

        $this->extensionMock->expects($this->once())
            ->method('getFileInfo')
            ->willReturn($imageContent);

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
