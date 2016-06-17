<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Framework\App\Filesystem\DirectoryList;

class FileProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlEncoder;

    /**
     * @var FileProcessor
     */
    private $model;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaDirectory;

    protected function setUp()
    {
        $this->mediaDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->getMockForAbstractClass();

        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);

        $this->uploaderFactory = $this->getMockBuilder('Magento\MediaStorage\Model\File\UploaderFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->getMockForAbstractClass();

        $this->urlEncoder = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')
            ->getMockForAbstractClass();

        $this->model = new FileProcessor(
            $this->filesystem,
            $this->uploaderFactory,
            $this->urlBuilder,
            $this->urlEncoder
        );
    }

    public function testGetAllowedExtensions()
    {
        $result = $this->model->getAllowedExtensions();

        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);
    }

    public function testSetAllowedExtensions()
    {
        $allowedExtensions = [
            'ext1',
            'ext2',
        ];

        $this->model->setAllowedExtensions($allowedExtensions);

        $result = $this->model->getAllowedExtensions();

        $this->assertTrue(is_array($result));
        $this->assertEquals($allowedExtensions, $result);
    }

    public function testGetStat()
    {
        $fileName = '/filename.ext1';

        $this->mediaDirectory->expects($this->once())
            ->method('stat')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . $fileName)
            ->willReturn(['size' => 1]);

        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $result = $this->model->getStat($fileName);

        $this->assertTrue(is_array($result));
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

        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertTrue($this->model->isExist($fileName));
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

        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertEquals($fileUrl, $this->model->getViewUrl($filePath, 'image'));
    }

    public function testGetViewUrlCustomerAddress()
    {
        $filePath = 'filename.ext1';
        $encodedFilePath = 'encodedfilenameext1';

        $baseUrl = 'baseUrl';
        $relativeUrl = 'relativeUrl';

        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA])
            ->willReturn($baseUrl);

        $this->mediaDirectory->expects($this->once())
            ->method('getRelativePath')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS . '/' . $filePath)
            ->willReturn($relativeUrl);

        $this->model->setEntityType(AddressMetadataInterface::ENTITY_TYPE_ADDRESS);
        $this->assertEquals($baseUrl . $relativeUrl, $this->model->getViewUrl($filePath, 'image'));
    }

    public function testRemoveUploadedFile()
    {
        $fileName = '/filename.ext1';

        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . $fileName)
            ->willReturn(true);

        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertTrue($this->model->removeUploadedFile($fileName));
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
            'path' => 'filepath',
        ];

        $uploaderMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Uploader')
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
            ->willReturn($expectedResult);

        $this->uploaderFactory->expects($this->once())
            ->method('create')
            ->with(['fileId' => 'customer[' . $attributeCode . ']'])
            ->willReturn($uploaderMock);

        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . FileProcessor::TMP_DIR)
            ->willReturn($absolutePath);

        $this->model->setAllowedExtensions($allowedExtensions);
        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $result = $this->model->saveTemporaryFile('customer[' . $attributeCode . ']');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File can not be saved to the destination folder.
     */
    public function testSaveTemporaryFileWithError()
    {
        $attributeCode = 'img1';

        $allowedExtensions = [
            'ext1',
            'ext2',
        ];

        $absolutePath = '/absolute/filepath';

        $uploaderMock = $this->getMockBuilder('Magento\MediaStorage\Model\File\Uploader')
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

        $this->model->setAllowedExtensions($allowedExtensions);
        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->model->saveTemporaryFile('customer[' . $attributeCode . ']');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Unable to create directory customer/f/i
     */
    public function testMoveTemporaryFileUnableToCreateDirectory()
    {
        $filePath = '/filename.ext1';

        $destinationPath = 'customer/f/i';

        $this->mediaDirectory->expects($this->once())
            ->method('create')
            ->with($destinationPath)
            ->willReturn(false);

        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->model->moveTemporaryFile($filePath);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Destination folder is not writable or does not exists
     */
    public function testMoveTemporaryFileDestinationFolderDoesNotExists()
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
            ->willReturn(false);

        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->model->moveTemporaryFile($filePath);
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

        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertEquals('/f/i' . $filePath, $this->model->moveTemporaryFile($filePath));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong while saving the file
     */
    public function testMoveTemporaryFileWithException()
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
            ->willThrowException(new \Exception('Exception.'));

        $this->model->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->model->moveTemporaryFile($filePath);
    }
}
