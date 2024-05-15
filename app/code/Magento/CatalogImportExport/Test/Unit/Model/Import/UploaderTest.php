<?php declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Driver\Http;
use Magento\Framework\Filesystem\Driver\Https;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Math\Random;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploaderTest extends TestCase
{
    /**
     * @var Database|MockObject
     */
    protected $coreFileStorageDb;

    /**
     * @var Storage|MockObject
     */
    protected $coreFileStorage;

    /**
     * @var AdapterFactory|MockObject
     */
    protected $imageFactory;

    /**
     * @var NotProtectedExtension|MockObject
     */
    protected $validator;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var ReadFactory|MockObject
     */
    protected $readFactory;

    /**
     * @var WriteInterface|MockObject
     */
    protected $directoryMock;

    /**
     * @var Random|MockObject
     */
    private $random;

    /**
     * @var Uploader|MockObject
     */
    protected $uploader;

    /**
     * @var TargetDirectory|MockObject
     */
    private $targetDirectory;

    protected function setUp(): void
    {
        $this->coreFileStorageDb = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreFileStorage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageFactory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = $this->getMockBuilder(
            NotProtectedExtension::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->readFactory = $this->getMockBuilder(ReadFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->directoryMock = $this->getMockBuilder(Write::class)
            ->onlyMethods(['writeFile', 'getRelativePath', 'isWritable', 'getAbsolutePath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDirectoryWrite'])
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryMock);

        $this->random = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRandomString'])
            ->getMock();

        $this->targetDirectory = $this->getMockBuilder(TargetDirectory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDirectoryWrite', 'getDirectoryRead'])
            ->getMock();
        $this->targetDirectory->method('getDirectoryWrite')->willReturn($this->directoryMock);
        $this->targetDirectory->method('getDirectoryRead')->willReturn($this->directoryMock);

        $this->uploader = $this->getMockBuilder(Uploader::class)
            ->setConstructorArgs(
                [
                    $this->coreFileStorageDb,
                    $this->coreFileStorage,
                    $this->imageFactory,
                    $this->validator,
                    $this->filesystem,
                    $this->readFactory,
                    null,
                    $this->random,
                    $this->targetDirectory
                ]
            )
            ->onlyMethods(['_setUploadFile', 'save', 'getTmpDir', 'checkAllowedExtension'])
            ->getMock();
    }

    /**
     * @dataProvider moveFileUrlDataProvider
     * @param $fileUrl
     * @param $expectedHost
     * @param $expectedFileName
     * @param $checkAllowedExtension
     * @throws LocalizedException
     */
    public function testMoveFileUrl($fileUrl, $expectedHost, $expectedFileName, $checkAllowedExtension)
    {
        $tmpDir = 'var/tmp';
        $destDir = 'var/dest/dir';
        $this->uploader->method('getTmpDir')->willReturn($tmpDir);

        // Expected invocation to validate file extension
        $this->uploader->expects($this->exactly($checkAllowedExtension))->method('checkAllowedExtension')
            ->willReturn(true);

        // Expected invocation to generate random string for file name postfix
        $this->random->expects($this->once())->method('getRandomString')
            ->with(16)
            ->willReturn('38GcEmPFKXXR8NMj');

        // Expected invocation to build the temp file path with the correct directory and filename
        $this->directoryMock->expects($this->any())->method('getRelativePath')
            ->with($tmpDir . '/' . $expectedFileName);

        // Create adjusted reader which does not validate path.
        $readMock = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['readAll'])
            ->getMock();

        // Expected invocations to create reader and read contents from url
        $this->readFactory->expects($this->once())->method('create')
            ->with($expectedHost)
            ->willReturn($readMock);
        $readMock->expects($this->once())->method('readAll')
            ->willReturn(null);

        // Expected invocation to write the temp file
        $this->directoryMock->expects($this->any())->method('writeFile')
            ->willReturn($expectedFileName);

        // Expected invocations save the downloaded file to temp file
        // and move the temp file to the destination directory
        $this->directoryMock->expects($this->exactly(2))
            ->method('isWritable')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$destDir] => true,
                [$tmpDir] => true
            });

        $this->directoryMock->expects($this->once())->method('getAbsolutePath')
            ->with($destDir)
            ->willReturn($destDir . '/' . $expectedFileName);
        $this->uploader->expects($this->once())->method('_setUploadFile')
            ->willReturnSelf();

        $returnFile = $destDir . DIRECTORY_SEPARATOR . $expectedFileName;

        $this->uploader->expects($this->once())->method('save')
            ->with($destDir . '/' . $expectedFileName)
            ->willReturn([
                'name' => $expectedFileName,
                'path' => 'absPath',
                'file' => $returnFile
            ]);

        $this->uploader->setDestDir($destDir);
        $result = $this->uploader->move($fileUrl);

        $this->assertEquals(['name' => $expectedFileName, 'file' => $returnFile], $result);
        $this->assertArrayNotHasKey('path', $result);
    }

    public function testMoveFileName()
    {
        $destDir = 'var/dest/dir';
        $fileName = 'test_uploader_file';
        $expectedRelativeFilePath = $fileName;
        $this->directoryMock->expects($this->once())->method('isWritable')->with($destDir)->willReturn(true);
        $this->directoryMock->expects($this->any())->method('getRelativePath')->with($expectedRelativeFilePath);
        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($destDir)
            ->willReturn($destDir . '/' . $fileName);
        //Check invoking of getTmpDir(), _setUploadFile(), save() methods.
        $this->uploader->expects($this->once())->method('getTmpDir')->willReturn('');
        $this->uploader->expects($this->once())->method('_setUploadFile')->willReturnSelf();

        $returnFile = $destDir . DIRECTORY_SEPARATOR . $fileName;

        $this->uploader->expects($this->once())->method('save')->with($destDir . '/' . $fileName)
            ->willReturn(['name' => $fileName, 'file' => $returnFile]);

        $this->uploader->setDestDir($destDir);
        $this->assertEquals(['name' => $fileName, 'file' => $returnFile], $this->uploader->move($fileName));
    }

    public function testFilenameLength()
    {
        $destDir = 'var/tmp/' . str_repeat('testFilenameLength', 13); // 242 characters

        $fileName = \uniqid(); // 13 characters

        $this->directoryMock->expects($this->once())
            ->method('isWritable')
            ->with($destDir)
            ->willReturn(true);

        $this->directoryMock->expects($this->once())
            ->method('getRelativePath')
            ->with($fileName)
            ->willReturn(null);

        $this->directoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($destDir)
            ->willReturn($destDir);

        $this->uploader->expects($this->once())
            ->method('save')
            ->with($destDir)
            ->willReturn([
                'name' => $fileName,
                'file' => $destDir . DIRECTORY_SEPARATOR . $fileName // 256 characters
            ]);

        $this->uploader->setDestDir($destDir);

        $this->expectException(\LengthException::class);

        $this->uploader->move($fileName);
    }

    /**
     * @dataProvider moveFileUrlDriverPoolDataProvider
     */
    public function testMoveFileUrlDrivePool($fileUrl, $expectedHost, $expectedDriverPool, $expectedScheme)
    {
        $driverPool = $this->createPartialMock(DriverPool::class, ['getDriver']);
        $driverMock = $this->getMockBuilder($expectedDriverPool)
            ->disableOriginalConstructor()
            ->addMethods(['readAll'])
            ->onlyMethods(['isExists'])
            ->getMock();
        $driverMock->method('isExists')->willReturn(true);
        $driverMock->method('readAll')->willReturn(null);
        $driverPool->method('getDriver')->willReturn($driverMock);

        $readFactory = $this->getMockBuilder(ReadFactory::class)
            ->setConstructorArgs(
                [
                    $driverPool,
                ]
            )
            ->onlyMethods(['create'])
            ->getMock();

        $readFactory->method('create')
            ->with($expectedHost, $expectedScheme)
            ->willReturn($driverMock);

        /** @var Uploader $uploaderMock */
        $uploaderMock = $this->getMockBuilder(Uploader::class)
            ->setConstructorArgs(
                [
                    $this->coreFileStorageDb,
                    $this->coreFileStorage,
                    $this->imageFactory,
                    $this->validator,
                    $this->filesystem,
                    $readFactory,
                    null,
                    $this->random,
                    $this->targetDirectory
                ]
            )
            ->getMock();

        $result = $uploaderMock->move($fileUrl);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public static function moveFileUrlDriverPoolDataProvider()
    {
        return [
            [
                '$fileUrl'              => 'http://test_uploader_file',
                '$expectedHost'         => 'test_uploader_file',
                '$expectedDriverPool'   => Http::class,
                '$expectedScheme'       => DriverPool::HTTP,
            ],
            [
                '$fileUrl'              => 'https://!:^&`;file',
                '$expectedHost'         => '!:^&`;file',
                '$expectedDriverPool'   => Https::class,
                '$expectedScheme'       => DriverPool::HTTPS,
            ],
        ];
    }

    /**
     * @return array
     */
    public static function moveFileUrlDataProvider()
    {
        return [
            'https_no_file_ext' => [
                '$fileUrl' => 'https://test_uploader_file',
                '$expectedHost' => 'test_uploader_file',
                '$expectedFileName' => 'test_uploader_file_38GcEmPFKXXR8NMj',
                '$checkAllowedExtension' => 0
            ],
            'https_invalid_chars' => [
                '$fileUrl' => 'https://www.google.com/!:^&`;image.jpg',
                '$expectedHost' => 'www.google.com/!:^&`;image.jpg',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ],
            'https_invalid_chars_no_file_ext' => [
                '$fileUrl' => 'https://!:^&`;image',
                '$expectedHost' => '!:^&`;image',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj',
                '$checkAllowedExtension' => 0
            ],
            'http_jpg' => [
                '$fileUrl' => 'http://www.google.com/image.jpg',
                '$expectedHost' => 'www.google.com/image.jpg',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ],
            'https_jpg' => [
                '$fileUrl' => 'https://www.google.com/image.jpg',
                '$expectedHost' => 'www.google.com/image.jpg',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ],
            'https_jpeg' => [
                '$fileUrl' => 'https://www.google.com/image.jpeg',
                '$expectedHost' => 'www.google.com/image.jpeg',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpeg',
                '$checkAllowedExtension' => 1
            ],
            'https_png' => [
                '$fileUrl' => 'https://www.google.com/image.png',
                '$expectedHost' => 'www.google.com/image.png',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.png',
                '$checkAllowedExtension' => 1
            ],
            'https_gif' => [
                '$fileUrl' => 'https://www.google.com/image.gif',
                '$expectedHost' => 'www.google.com/image.gif',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.gif',
                '$checkAllowedExtension' => 1
            ],
            'https_one_query_param' => [
                '$fileUrl' => 'https://www.google.com/image.jpg?param=1',
                '$expectedHost' => 'www.google.com/image.jpg?param=1',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ],
            'https_two_query_params' => [
                '$fileUrl' => 'https://www.google.com/image.jpg?param=1&param=2',
                '$expectedHost' => 'www.google.com/image.jpg?param=1&param=2',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ]
        ];
    }
}
