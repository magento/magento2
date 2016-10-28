<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Api\Uploader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uploaderMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryWriteMock;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->directoryWriteMock = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\WriteInterface'
        );
        $this->fileSystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWriteMock);
        $this->contentValidatorMock = $this->getMockBuilder('Magento\Framework\Api\ImageContentValidatorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder('Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderMock = $this->getMockBuilder('Magento\Framework\Api\Uploader')
            ->setMethods(
                [
                    'processFileAttributes',
                    'setFilesDispersion',
                    'setFilenamesCaseSensitivity',
                    'setAllowRenameFiles',
                    'save',
                    'getUploadedFileName'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageProcessor = $this->objectManager->getObject(
            'Magento\Framework\Api\ImageProcessor',
            [
                'fileSystem' => $this->fileSystemMock,
                'contentValidator' => $this->contentValidatorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'logger' => $this->loggerMock,
                'uploader' => $this->uploaderMock
            ]
        );
    }

    public function testSaveWithNoImageData()
    {
        $imageDataMock = $this->getMockBuilder('Magento\Framework\Api\CustomAttributesDataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataMock->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('getCustomAttributeValueByType')
            ->willReturn([]);

        $this->assertEquals($imageDataMock, $this->imageProcessor->save($imageDataMock, 'testEntityType'));
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

        $this->imageProcessor->save($imageDataMock, 'testEntityType');
    }

    public function testSaveWithNoPreviousData()
    {
        $imageContent = $this->getMockBuilder('Magento\Framework\Api\Data\ImageContentInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn('testImageData');
        $imageContent->expects($this->any())
            ->method('getName')
            ->willReturn('testFileName');
        $imageContent->expects($this->any())
            ->method('getType')
            ->willReturn('image/jpg');

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

        $this->contentValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->directoryWriteMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn('testPath');

        $this->assertEquals($imageData, $this->imageProcessor->save($imageData, 'testEntityType'));
    }

    public function testSaveWithPreviousData()
    {
        $imageContent = $this->getMockBuilder('Magento\Framework\Api\Data\ImageContentInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn('testImageData');
        $imageContent->expects($this->any())
            ->method('getName')
            ->willReturn('testFileName');
        $imageContent->expects($this->any())
            ->method('getType')
            ->willReturn('image/jpg');

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

        $this->contentValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->directoryWriteMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn('testPath');

        $prevImageAttribute = $this->getMockForAbstractClass('Magento\Framework\Api\AttributeInterface');
        $prevImageAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn('testImagePath');

        $prevImageData = $this->getMockForAbstractClass('Magento\Framework\Api\CustomAttributesDataInterface');
        $prevImageData->expects($this->once())
            ->method('getCustomAttribute')
            ->willReturn($prevImageAttribute);

        $this->assertEquals($imageData, $this->imageProcessor->save($imageData, 'testEntityType', $prevImageData));
    }
}
