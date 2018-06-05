<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\CustomOptions;

use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor as FileProcessor;

class CustomOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomOption
     */
    protected $model;

    /**
     * @var FileProcessor | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileProcessor;

    protected function setUp()
    {
        $context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $extensionAttributesFactory = $this->getMockBuilder('Magento\Framework\Api\ExtensionAttributesFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $attributeValueFactory = $this->getMockBuilder('Magento\Framework\Api\AttributeValueFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessor = $this->getMockBuilder('Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $resource = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\AbstractResource')
            ->disableOriginalConstructor()
            ->getMock();

        $collection = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CustomOption(
            $context,
            $registry,
            $extensionAttributesFactory,
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

        $this->model->setData(\Magento\Catalog\Api\Data\CustomOptionInterface::OPTION_VALUE, 'test');
        $this->assertEquals('test', $this->model->getOptionValue());

        $this->model->setData(\Magento\Catalog\Api\Data\CustomOptionInterface::OPTION_VALUE, 'file');
        $this->assertEquals('file', $this->model->getOptionValue());
    }

    public function testGetOptionValueWithFileInfo()
    {
        $customOption = $this->getMockBuilder('Magento\Catalog\Api\Data\CustomOptionExtensionInterface')
            ->setMethods(['getFileInfo'])
            ->getMockForAbstractClass();

        $imageContent = $this->getMockBuilder('Magento\Framework\Api\Data\ImageContentInterface')
            ->getMockForAbstractClass();

        $customOption->expects($this->once())
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

        $this->model->setExtensionAttributes($customOption);
        $this->model->setData(\Magento\Catalog\Api\Data\CustomOptionInterface::OPTION_VALUE, 'file');
        $this->assertEquals($imageResult, $this->model->getOptionValue());
    }

    public function testSetOptionValue()
    {
        $this->model->setOptionValue('test');
        $this->assertEquals('test', $this->model->getOptionValue());
    }
}
