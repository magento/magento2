<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Api\Test\Unit\Api;

/**
 * Unit test class for \Magento\Framework\Api\ImageProcessor
 */
class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystemMock;

    /**
     * @var \Magento\Framework\Api\ImageContentValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \Magento\Framework\Api\Data\EavImageContentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavImageContentFactoryMock;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->fileSystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentValidatorMock = $this->getMockBuilder('Magento\Framework\Api\ImageContentValidatorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder('Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavImageContentFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Api\Data\EavImageContentInterfaceFactory'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageProcessor = $this->objectManager->getObject(
            'Magento\Framework\Api\ImageProcessor',
            [
                'fileSystem' => $this->fileSystemMock,
                'contentValidator' => $this->contentValidatorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'eavImageContentFactory' => $this->eavImageContentFactoryMock,
            ]
        );
    }

    public function testSaveWithNoImageData()
    {
        $imageData = $this->getMockBuilder('Magento\Framework\Api\CustomAttributesDataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $imageData->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('getCustomAttributeValueByType')
            ->willReturn([]);

        $this->assertEquals($imageData, $this->imageProcessor->save($imageData));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image content is not valid.
     */
    public function testSaveInputException()
    {
        $imageContent = $this->getMockBuilder('Magento\Framework\Api\Data\ImageContentInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataObject = $this->getMockBuilder('Magento\Framework\Api\AttributeValue')
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataObject->expects($this->once())
            ->method('getValue')
            ->willReturn($imageContent);

        $imageDataMock = $this->getMockBuilder('Magento\Framework\Api\CustomAttributesDataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataMock->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('getCustomAttributeValueByType')
            ->willReturn([$imageDataObject]);

        $this->contentValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->imageProcessor->save($imageDataMock);
    }

    public function testSave()
    {
        $imageContent = $this->getMockBuilder('Magento\Framework\Api\Data\ImageContentInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn('testImageData');
        $imageContent->expects($this->once())
            ->method('getName')
            ->willReturn('testFileName');

        $imageDataObject = $this->getMockBuilder('Magento\Framework\Api\AttributeValue')
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataObject->expects($this->once())
            ->method('getValue')
            ->willReturn($imageContent);

        $imageData = $this->getMockForAbstractClass('Magento\Framework\Api\CustomAttributesDataInterface');
        $imageData->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('getCustomAttributeValueByType')
            ->willReturn([$imageDataObject]);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('mergeDataObjects')
            ->willReturnSelf();

        $this->contentValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $directoryWrite = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');

        $directoryWrite->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('testPath');

        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($directoryWrite);

        $eavImageContent = $this->getMockForAbstractClass('Magento\Framework\Api\Data\EavImageContentInterface');

        $this->eavImageContentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eavImageContent);

        $this->assertEquals($imageData, $this->imageProcessor->save($imageData));
    }
}
