<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileProcessorTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var UploaderFactory|MockObject
     */
    private $uploaderFactory;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var EncoderInterface|MockObject
     */
    private $urlEncoder;

    /**
     * @var WriteInterface|MockObject
     */
    private $mediaDirectory;

    /**
     * @var Mime|MockObject
     */
    private $mime;

    protected function setUp(): void
    {
        $this->mediaDirectory = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);

        $this->uploaderFactory = $this->getMockBuilder(UploaderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $this->urlEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->getMockForAbstractClass();

        $this->mime = $this->getMockBuilder(Mime::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $entityTypeCode
     * @param array $allowedExtensions
     * @return FileProcessor
     */
    private function getModel($entityTypeCode, array $allowedExtensions = [])
    {
        $model = new FileProcessor(
            $this->filesystem,
            $this->uploaderFactory,
            $this->urlBuilder,
            $this->urlEncoder,
            $entityTypeCode,
            $this->mime,
            $allowedExtensions
        );
        return $model;
    }

    public function testGetStat()
    {
        $fileName = '/filename.ext1';

        $this->mediaDirectory->expects($this->once())
            ->method('stat')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . $fileName)
            ->willReturn(['size' => 1]);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $result = $model->getStat($fileName);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('size', $result);
        $this->assertEquals(1, $result['size']);
    }

    public function testIsExist()
    {
        $fileName = '/filename.ext1';

        $this->mediaDirectory->expects($this->once())
            ->method('isExist')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . $fileName)
            ->willReturn(true);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertTrue($model->isExist($fileName));
    }

    public function testGetViewUrlCustomer()
    {
        $filePath = 'filename.ext1';
        $encodedFilePath = 'encodedfilenameext1';

        $fileUrl = 'fileUrl';

        $this->urlEncoder->expects($this->once())
            ->method('encode')
            ->with($filePath)
            ->willReturn($encodedFilePath);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('customer/index/viewfile', ['image' => $encodedFilePath])
            ->willReturn($fileUrl);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertEquals($fileUrl, $model->getViewUrl($filePath, 'image'));
    }

    public function testGetViewUrlCustomerAddress()
    {
        $filePath = 'filename.ext1';

        $baseUrl = 'baseUrl';
        $relativeUrl = 'relativeUrl';

        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => UrlInterface::URL_TYPE_MEDIA])
            ->willReturn($baseUrl);

        $this->mediaDirectory->expects($this->once())
            ->method('getRelativePath')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS . '/' . $filePath)
            ->willReturn($relativeUrl);

        $model = $this->getModel(AddressMetadataInterface::ENTITY_TYPE_ADDRESS);
        $this->assertEquals($baseUrl . $relativeUrl, $model->getViewUrl($filePath, 'image'));
    }

    public function testRemoveUploadedFile()
    {
        $fileName = '/filename.ext1';

        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . $fileName)
            ->willReturn(true);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertTrue($model->removeUploadedFile($fileName));
    }

    public function testSaveTemporaryFile()
    {
        $attributeCode = 'img1';

        $allowedExtensions = [
            'ext1',
            'ext2',
        ];

        $absolutePath = '/absolute/filepath';

        $expectedResult = [
            'file' => 'filename.ext1',
        ];
        $resultWithPath = [
            'file' => 'filename.ext1',
            'path' => 'filepath'
        ];

        $uploaderMock = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uploaderMock->expects($this->once())
            ->method('setFilesDispersion')
            ->with(false)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setFilenamesCaseSensitivity')
            ->with(false)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setAllowedExtensions')
            ->with($allowedExtensions)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('save')
            ->with($absolutePath)
            ->willReturn($resultWithPath);

        $this->uploaderFactory->expects($this->once())
            ->method('create')
            ->with(['fileId' => 'customer[' . $attributeCode . ']'])
            ->willReturn($uploaderMock);

        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . FileProcessor::TMP_DIR)
            ->willReturn($absolutePath);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $allowedExtensions);
        $result = $model->saveTemporaryFile('customer[' . $attributeCode . ']');

        $this->assertEquals($expectedResult, $result);
    }

    public function testSaveTemporaryFileWithError()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('File can not be saved to the destination folder.');

        $attributeCode = 'img1';

        $allowedExtensions = [
            'ext1',
            'ext2',
        ];

        $absolutePath = '/absolute/filepath';

        $uploaderMock = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uploaderMock->expects($this->once())
            ->method('setFilesDispersion')
            ->with(false)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setFilenamesCaseSensitivity')
            ->with(false)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setAllowedExtensions')
            ->with($allowedExtensions)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('save')
            ->with($absolutePath)
            ->willReturn(false);

        $this->uploaderFactory->expects($this->once())
            ->method('create')
            ->with(['fileId' => 'customer[' . $attributeCode . ']'])
            ->willReturn($uploaderMock);

        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . FileProcessor::TMP_DIR)
            ->willReturn($absolutePath);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $allowedExtensions);
        $model->saveTemporaryFile('customer[' . $attributeCode . ']');
    }

    public function testMoveTemporaryFileUnableToCreateDirectory()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unable to create directory customer/f/i');

        $filePath = '/filename.ext1';

        $destinationPath = 'customer/f/i';

        $this->mediaDirectory->expects($this->once())
            ->method('create')
            ->with($destinationPath)
            ->willReturn(false);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $model->moveTemporaryFile($filePath);
    }

    public function testMoveTemporaryFileDestinationFolderDoesNotExists()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Destination folder is not writable or does not exists');

        $filePath = '/filename.ext1';

        $destinationPath = 'customer/f/i';

        $this->mediaDirectory->expects($this->once())
            ->method('create')
            ->with($destinationPath)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('isWritable')
            ->with($destinationPath)
            ->willReturn(false);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $model->moveTemporaryFile($filePath);
    }

    public function testMoveTemporaryFile()
    {
        $filePath = '/filename.ext1';

        $destinationPath = 'customer/f/i';

        $this->mediaDirectory->expects($this->once())
            ->method('create')
            ->with($destinationPath)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('isWritable')
            ->with($destinationPath)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with($destinationPath)
            ->willReturn('/' . $destinationPath);

        $path = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . FileProcessor::TMP_DIR . $filePath;
        $newPath = $destinationPath . $filePath;

        $this->mediaDirectory->expects($this->once())
            ->method('renameFile')
            ->with($path, $newPath)
            ->willReturn(true);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertEquals('/f/i' . $filePath, $model->moveTemporaryFile($filePath));
    }

    public function testMoveTemporaryFileWithException()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while saving the file');

        $filePath = '/filename.ext1';

        $destinationPath = 'customer/f/i';

        $this->mediaDirectory->expects($this->once())
            ->method('create')
            ->with($destinationPath)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('isWritable')
            ->with($destinationPath)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with($destinationPath)
            ->willReturn('/' . $destinationPath);

        $path = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . FileProcessor::TMP_DIR . $filePath;
        $newPath = $destinationPath . $filePath;

        $this->mediaDirectory->expects($this->once())
            ->method('renameFile')
            ->with($path, $newPath)
            ->willThrowException(new \Exception('Exception.'));

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $model->moveTemporaryFile($filePath);
    }

    public function testGetMimeType()
    {
        $fileName = '/filename.ext1';
        $absoluteFilePath = '/absolute_path/customer/filename.ext1';

        $expected = 'ext1';

        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . ltrim($fileName, '/'))
            ->willReturn($absoluteFilePath);

        $this->mime->expects($this->once())
            ->method('getMimeType')
            ->with($absoluteFilePath)
            ->willReturn($expected);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $this->assertEquals($expected, $model->getMimeType($fileName));
    }
}
