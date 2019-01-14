<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorageDb;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorage;

    /**
     * @var \Magento\Framework\Image\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryResolver;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Uploader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uploader;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->coreFileStorageDb = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreFileStorage = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageFactory = $this->getMockBuilder(\Magento\Framework\Image\AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = $this->getMockBuilder(
            \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class
        )->disableOriginalConstructor()->getMock();

        $this->readFactory = $this->getMockBuilder(\Magento\Framework\Filesystem\File\ReadFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Write::class)
            ->setMethods(['writeFile', 'getRelativePath', 'isWritable', 'isReadable', 'getAbsolutePath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite'])
            ->getMock();

        $this->directoryResolver = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['validatePath'])
            ->getMock();

        $this->filesystem->expects($this->any())
                        ->method('getDirectoryWrite')
                        ->will($this->returnValue($this->directoryMock));

        $this->uploader = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Uploader::class)
            ->setConstructorArgs([
                $this->coreFileStorageDb,
                $this->coreFileStorage,
                $this->imageFactory,
                $this->validator,
                $this->filesystem,
                $this->readFactory,
                null,
                $this->directoryResolver
            ])
            ->setMethods(['_setUploadFile', 'save', 'getTmpDir', 'checkAllowedExtension'])
            ->getMock();
    }

    /**
     * @dataProvider moveFileUrlDataProvider
     * @param string $fileUrl
     * @param string $expectedHost
     * @param string $expectedFileName
     * @param int $checkAllowedExtension
     * @return void
     */
    public function testMoveFileUrl(
        string $fileUrl,
        string $expectedHost,
        string $expectedFileName,
        int $checkAllowedExtension
    ) {
        $destDir = 'var/dest/dir';
        $expectedRelativeFilePath = $expectedFileName;
        $this->directoryMock->expects($this->once())->method('isWritable')->with($destDir)->willReturn(true);
        $this->directoryMock->expects($this->any())->method('getRelativePath')->with($expectedRelativeFilePath);
        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($destDir)
            ->willReturn($destDir . '/' . $expectedFileName);
        // Check writeFile() method invoking.
        $this->directoryMock->expects($this->any())->method('writeFile')->willReturn($expectedFileName);

        // Create adjusted reader which does not validate path.
        $readMock = $this->getMockBuilder(\Magento\Framework\Filesystem\File\Read::class)
            ->disableOriginalConstructor()
            ->setMethods(['readAll'])
            ->getMock();
        // Check readAll() method invoking.
        $readMock->expects($this->once())->method('readAll')->willReturn(null);

        // Check create() method invoking with expected argument.
        $this->readFactory->expects($this->once())
                        ->method('create')
                        ->will($this->returnValue($readMock))->with($expectedHost);
        //Check invoking of getTmpDir(), _setUploadFile(), save() methods.
        $this->uploader->expects($this->any())->method('getTmpDir')->willReturn('');
        $this->uploader->expects($this->once())->method('_setUploadFile')->willReturnSelf();
        $this->uploader->expects($this->once())->method('save')->with($destDir . '/' . $expectedFileName)
            ->willReturn(['name' => $expectedFileName, 'path' => 'absPath']);
        $this->uploader->expects($this->exactly($checkAllowedExtension))
            ->method('checkAllowedExtension')
            ->willReturn(true);

        $this->uploader->setDestDir($destDir);
        $result = $this->uploader->move($fileUrl);
        $this->assertEquals(['name' => $expectedFileName], $result);
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
        $this->uploader->expects($this->once())->method('save')->with($destDir . '/' . $fileName)
            ->willReturn(['name' => $fileName]);

        $this->uploader->setDestDir($destDir);
        $this->assertEquals(['name' => $fileName], $this->uploader->move($fileName));
    }

    /**
     * @dataProvider moveFileUrlDriverPoolDataProvider
     */
    public function testMoveFileUrlDrivePool($fileUrl, $expectedHost, $expectedDriverPool, $expectedScheme)
    {
        $driverPool = $this->createPartialMock(\Magento\Framework\Filesystem\DriverPool::class, ['getDriver']);
        $driverMock = $this->createPartialMock($expectedDriverPool, ['readAll', 'isExists']);
        $driverMock->expects($this->any())->method('isExists')->willReturn(true);
        $driverMock->expects($this->any())->method('readAll')->willReturn(null);
        $driverPool->expects($this->any())->method('getDriver')->willReturn($driverMock);

        $readFactory = $this->getMockBuilder(\Magento\Framework\Filesystem\File\ReadFactory::class)
            ->setConstructorArgs(
                [
                    $driverPool,
                ]
            )
            ->setMethods(['create'])
            ->getMock();

        $readFactory->expects($this->any())->method('create')
            ->with($expectedHost, $expectedScheme)
            ->willReturn($driverMock);

        $uploaderMock = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Uploader::class)
            ->setConstructorArgs([
                $this->coreFileStorageDb,
                $this->coreFileStorage,
                $this->imageFactory,
                $this->validator,
                $this->filesystem,
                $readFactory,
            ])
            ->getMock();

        $result = $uploaderMock->move($fileUrl);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function moveFileUrlDriverPoolDataProvider()
    {
        return [
            [
                '$fileUrl'              => 'http://test_uploader_file',
                '$expectedHost'         => 'test_uploader_file',
                '$expectedDriverPool'   => \Magento\Framework\Filesystem\Driver\Http::class,
                '$expectedScheme'       => \Magento\Framework\Filesystem\DriverPool::HTTP,
            ],
            [
                '$fileUrl'              => 'https://!:^&`;file',
                '$expectedHost'         => '!:^&`;file',
                '$expectedDriverPool'   => \Magento\Framework\Filesystem\Driver\Https::class,
                '$expectedScheme'       => \Magento\Framework\Filesystem\DriverPool::HTTPS,
            ],
        ];
    }

    /**
     * @return array
     */
    public function moveFileUrlDataProvider()
    {
        return [
            [
                '$fileUrl' => 'http://test_uploader_file',
                '$expectedHost' => 'test_uploader_file',
                '$expectedFileName' => 'test_uploader_file',
                '$checkAllowedExtension' => 0,
            ],
            [
                '$fileUrl' => 'https://!:^&`;file',
                '$expectedHost' => '!:^&`;file',
                '$expectedFileName' => 'file',
                '$checkAllowedExtension' => 0,
            ],
            [
                '$fileUrl' => 'https://www.google.com/image.jpg',
                '$expectedHost' => 'www.google.com/image.jpg',
                '$expectedFileName' => 'image.jpg',
                '$checkAllowedExtension' => 1,
            ],
            [
                '$fileUrl' => 'https://www.google.com/image.jpg?param=1',
                '$expectedHost' => 'www.google.com/image.jpg?param=1',
                '$expectedFileName' => 'image.jpg',
                '$checkAllowedExtension' => 1,
            ],
            [
                '$fileUrl' => 'https://www.google.com/image.jpg?param=1&param=2',
                '$expectedHost' => 'www.google.com/image.jpg?param=1&param=2',
                '$expectedFileName' => 'image.jpg',
                '$checkAllowedExtension' => 1,
            ],
            [
                '$fileUrl' => 'http://www.google.com/image.jpg?param=1&param=2',
                '$expectedHost' => 'www.google.com/image.jpg?param=1&param=2',
                '$expectedFileName' => 'image.jpg',
                '$checkAllowedExtension' => 1,
            ],
        ];
    }

    /**
     * @dataProvider validatePathDataProvider
     *
     * @param bool $pathIsValid
     * @return void
     */
    public function testSetTmpDir(bool $pathIsValid)
    {
        $path = 'path';
        $absolutePath = 'absolute_path';
        $this->directoryMock->expects($this->atLeastOnce())->method('isReadable')->with($path)->willReturn(true);
        $this->directoryMock->expects($this->atLeastOnce())->method('getAbsolutePath')->with($path)
            ->willReturn($absolutePath);
        $this->directoryResolver->expects($this->atLeastOnce())->method('validatePath')->with($absolutePath, 'base')
            ->willReturn($pathIsValid);

        $this->assertEquals($pathIsValid, $this->uploader->setTmpDir($path));
    }

    /**
     * Data provider for the testSetTmpDir()
     *
     * @return array
     */
    public function validatePathDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
