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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
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
     * @param string|null $customerFileUrlPath
     * @param string|null $customerAddressFileUrlPath
     * @return FileProcessor
     */
    private function getModel(
        $entityTypeCode,
        array $allowedExtensions = [],
        string $customerFileUrlPath = null,
        string $customerAddressFileUrlPath = null
    ) {
        $model = new FileProcessor(
            $this->filesystem,
            $this->uploaderFactory,
            $this->urlBuilder,
            $this->urlEncoder,
            $entityTypeCode,
            $this->mime,
            $allowedExtensions,
            $customerFileUrlPath ?? 'customer/index/viewfile',
            $customerAddressFileUrlPath ?? 'customer/address/viewfile'
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

    /**
     * @param array $params
     * @param string $filePath
     * @param string $expectedUrl
     * @dataProvider getViewUrlDataProvider
     */
    public function testGetViewUrlTest(
        array $params,
        string $filePath,
        string $expectedUrl
    ): void {
        $this->urlEncoder->expects($this->once())
            ->method('encode')
            ->willReturnCallback('md5');

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->willReturnCallback(
                function (string $path, array $params) {
                    $url = 'http://example.com/' . trim($path, '/');
                    foreach ($params as $key => $value) {
                        $url .= "/$key/$value";
                    }
                    return $url;
                }
            );

        $model = $this->getModel(
            $params['entityTypeCode'],
            [],
            $params['customerFileUrlPath'],
            $params['addressFileUrlPath']
        );
        $this->assertEquals($expectedUrl, $model->getViewUrl($filePath, 'file'));
    }

    /**
     * @return array[]
     */
    public function getViewUrlDataProvider(): array
    {
        return [
            [
                [
                    'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    'customerFileUrlPath' => 'customer/index/viewfile',
                    'addressFileUrlPath' => 'customer/address/viewfile',
                ],
                '/i/m/image1.jpeg',
                'http://example.com/customer/index/viewfile/file/57523c876842c97ab9d5fd92f8d8d9ec'
            ],
            [
                [
                    'entityTypeCode' => AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                    'customerFileUrlPath' => 'customer/index/viewfile',
                    'addressFileUrlPath' => 'customer/address/viewfile',
                ],
                '/i/m/image2.png',
                'http://example.com/customer/address/viewfile/file/4498819248a7f824893bd3dac4babdfc'
            ],
            [
                [
                    'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    'customerFileUrlPath' => 'custom_module/customer/preview',
                    'addressFileUrlPath' => 'custom_module/address/preview',
                ],
                '/i/m/image1.jpeg',
                'http://example.com/custom_module/customer/preview/file/57523c876842c97ab9d5fd92f8d8d9ec'
            ],
            [
                [
                    'entityTypeCode' => AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                    'customerFileUrlPath' => 'custom_module/customer/preview',
                    'addressFileUrlPath' => 'custom_module/address/preview',
                ],
                '/i/m/image2.png',
                'http://example.com/custom_module/address/preview/file/4498819248a7f824893bd3dac4babdfc'
            ]
        ];
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

        $this->configureMediaDirectoryMock($destinationPath, false);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $model->moveTemporaryFile($filePath);
    }

    public function testMoveTemporaryFileDestinationFolderDoesNotExists()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Destination folder is not writable or does not exists');

        $filePath = '/filename.ext1';

        $destinationPath = 'customer/f/i';

        $this->configureMediaDirectoryMock($destinationPath, true);
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

        $this->configureMediaDirectoryMock($destinationPath, true);
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

        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $mockFileSystem = $this->createMock(Filesystem::class);
        $mockRead = $this->createMock(ReadInterface::class);
        $objectManagerMock->method('get')->willReturn($mockFileSystem);
        $mockFileSystem->method('getDirectoryRead')->willReturn($mockRead);
        $mockRead->method('isExist')->willReturn(false);
        ObjectManager::setInstance($objectManagerMock);

        $this->mediaDirectory->expects($this->once())
            ->method('renameFile')
            ->with($path, $newPath)
            ->willReturn(true);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertEquals('/f/i' . $filePath, $model->moveTemporaryFile($filePath));
    }

    public function testMoveTemporaryFileNewFileName()
    {
        $filePath = '/filename.ext1';

        $destinationPath = 'customer/f/i';

        $this->configureMediaDirectoryMock($destinationPath, true);
        $this->mediaDirectory->expects($this->once())
            ->method('isWritable')
            ->with($destinationPath)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with($destinationPath)
            ->willReturn('/' . $destinationPath);

        $path = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . FileProcessor::TMP_DIR . $filePath;

        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $mockFileSystem = $this->createMock(Filesystem::class);
        $mockRead = $this->createMock(ReadInterface::class);
        $objectManagerMock->method('get')->willReturn($mockFileSystem);
        $mockFileSystem->method('getDirectoryRead')->willReturn($mockRead);
        $mockRead->method('isExist')->willReturnOnConsecutiveCalls(true, true, false);
        ObjectManager::setInstance($objectManagerMock);

        $this->mediaDirectory->expects($this->once())
            ->method('renameFile')
            ->with($path, 'customer/f/i/filename_2.ext1')
            ->willReturn(true);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->assertEquals('/f/i/filename_2.ext1', $model->moveTemporaryFile($filePath));
    }

    public function testMoveTemporaryFileWithException()
    {
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $mockFileSystem = $this->createMock(Filesystem::class);
        $mockRead = $this->createMock(ReadInterface::class);
        $objectManagerMock->method($this->logicalOr('get', 'create'))->willReturn($mockFileSystem);
        $mockFileSystem->method('getDirectoryRead')->willReturn($mockRead);
        $mockRead->method('isExist')->willReturn(false);
        ObjectManager::setInstance($objectManagerMock);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while saving the file');

        $filePath = '/filename.ext1';

        $destinationPath = 'customer/f/i';

        $this->configureMediaDirectoryMock($destinationPath, true);
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

    /**
     * Configure media directory mock to create media directory.
     *
     * @param string $destinationPath
     * @param bool $directoryCreated
     */
    private function configureMediaDirectoryMock(string $destinationPath, bool $directoryCreated): void
    {
        $this->mediaDirectory->expects($this->at(0))
            ->method('isExist')
            ->with('customer/tmp/filename.ext1')
            ->willReturn(true);
        $this->mediaDirectory->expects($this->at(1))
            ->method('isExist')
            ->with('customer/filename.ext1')
            ->willReturn(false);
        $this->mediaDirectory->expects($this->once())
            ->method('create')
            ->with($destinationPath)
            ->willReturn($directoryCreated);
    }
}
