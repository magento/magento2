<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\Api;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessor;
use Magento\Framework\Api\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test class for \Magento\Framework\Api\ImageProcessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageProcessorTest extends TestCase
{
    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Filesystem|MockObject
     */
    protected $fileSystemMock;

    /**
     * @var ImageContentValidatorInterface|MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var Uploader|MockObject
     */
    protected $uploaderMock;

    /**
     * @var WriteInterface|MockObject
     */
    protected $directoryWriteMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->directoryWriteMock = $this->getMockForAbstractClass(
            WriteInterface::class
        );
        $this->fileSystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWriteMock);
        $this->contentValidatorMock = $this->getMockBuilder(
            ImageContentValidatorInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->uploaderMock = $this->getMockBuilder(Uploader::class)
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
            ImageProcessor::class,
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
        $imageDataMock = $this->getMockBuilder(CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $imageDataMock->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('getCustomAttributeValueByType')
            ->willReturn([]);

        $this->assertEquals($imageDataMock, $this->imageProcessor->save($imageDataMock, 'testEntityType'));
    }

    public function testSaveInputException()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The image content is invalid. Verify the content and try again.');
        $imageContent = $this->getMockBuilder(ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $imageDataObject = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataObject->expects($this->once())
            ->method('getValue')
            ->willReturn($imageContent);

        $imageDataMock = $this->getMockBuilder(CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
        $imageContent = $this->getMockBuilder(ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn('testImageData');
        $imageContent->expects($this->any())
            ->method('getName')
            ->willReturn('testFileName');
        $imageContent->expects($this->any())
            ->method('getType')
            ->willReturn('image/jpg');

        $imageDataObject = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataObject->expects($this->once())
            ->method('getValue')
            ->willReturn($imageContent);

        $imageData = $this->getMockForAbstractClass(CustomAttributesDataInterface::class);
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
        $imageContent = $this->getMockBuilder(ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $imageContent->expects($this->any())
            ->method('getBase64EncodedData')
            ->willReturn('testImageData');
        $imageContent->expects($this->any())
            ->method('getName')
            ->willReturn('testFileName.png');

        $imageDataObject = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataObject->expects($this->once())
            ->method('getValue')
            ->willReturn($imageContent);

        $imageData = $this->getMockForAbstractClass(CustomAttributesDataInterface::class);
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

        $prevImageAttribute = $this->getMockForAbstractClass(AttributeInterface::class);
        $prevImageAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn('testImagePath');

        $prevImageData = $this->getMockForAbstractClass(CustomAttributesDataInterface::class);
        $prevImageData->expects($this->once())
            ->method('getCustomAttribute')
            ->willReturn($prevImageAttribute);

        $this->assertEquals($imageData, $this->imageProcessor->save($imageData, 'testEntityType', $prevImageData));
    }

    public function testSaveWithoutFileExtension()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Cannot recognize image extension.');
        $imageContent = $this->getMockBuilder(ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $imageContent->expects($this->once())
            ->method('getBase64EncodedData')
            ->willReturn('testImageData');
        $imageContent->expects($this->once())
            ->method('getName')
            ->willReturn('testFileName');

        $imageDataObject = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageDataObject->expects($this->once())
            ->method('getValue')
            ->willReturn($imageContent);

        $imageData = $this->getMockForAbstractClass(CustomAttributesDataInterface::class);
        $imageData->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('getCustomAttributeValueByType')
            ->willReturn([$imageDataObject]);

        $this->contentValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->assertEquals($imageData, $this->imageProcessor->save($imageData, 'testEntityType'));
    }
}
