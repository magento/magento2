<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

class UploaderTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    private $random;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Uploader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uploader;

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

        $this->directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Writer::class)
            ->setMethods(['writeFile', 'getRelativePath', 'isWritable', 'isReadable', 'getAbsolutePath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite'])
            ->getMock();
        $this->filesystem->expects($this->any())
                        ->method('getDirectoryWrite')
                        ->will($this->returnValue($this->directoryMock));

        $this->directoryResolver = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['validatePath'])
            ->getMock();

        $this->random = $this->getMockBuilder(\Magento\Framework\Math\Random::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRandomString'])
            ->getMock();

        $this->uploader = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Uploader::class)
            ->setConstructorArgs([
                $this->coreFileStorageDb,
                $this->coreFileStorage,
                $this->imageFactory,
                $this->validator,
                $this->filesystem,
                $this->readFactory,
                null,
                $this->directoryResolver,
                $this->random
            ])
            ->setMethods(['_setUploadFile', 'save', 'getTmpDir', 'checkAllowedExtension'])
            ->getMock();
    }

    /**
     * @dataProvider moveFileUrlDataProvider
     * @param $fileUrl
     * @param $expectedHost
     * @param $expectedFileName
     * @param $checkAllowedExtension
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testMoveFileUrl($fileUrl, $expectedHost, $expectedFileName, $checkAllowedExtension)
    {
        $tmpDir = 'var/tmp';
        $destDir = 'var/dest/dir';

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
        $readMock = $this->getMockBuilder(\Magento\Framework\Filesystem\File\Read::class)
            ->disableOriginalConstructor()
            ->setMethods(['readAll'])
            ->getMock();

        // Expected invocations to create reader and read contents from url
        $this->readFactory->expects($this->once())->method('create')
            ->with($expectedHost)
            ->will($this->returnValue($readMock));
        $readMock->expects($this->once())->method('readAll')
            ->will($this->returnValue(null));

        // Expected invocation to write the temp file
        $this->directoryMock->expects($this->any())->method('writeFile')
            ->will($this->returnValue($expectedFileName));

        // Expected invocations to move the temp file to the destination directory
        $this->directoryMock->expects($this->once())->method('isWritable')
            ->with($destDir)
            ->willReturn(true);
        $this->directoryMock->expects($this->once())->method('getAbsolutePath')
            ->with($destDir)
            ->willReturn($destDir . '/' . $expectedFileName);
        $this->uploader->expects($this->once())->method('_setUploadFile')
            ->willReturnSelf();
        $this->uploader->expects($this->once())->method('save')
            ->with($destDir . '/' . $expectedFileName)
            ->willReturn(['name' => $expectedFileName, 'path' => 'absPath']);

        // Do not use configured temp directory
        $this->uploader->expects($this->never())->method('getTmpDir');

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
        $this->uploader->expects($this->once())->method('getTmpDir')->will($this->returnValue(''));
        $this->uploader->expects($this->once())->method('_setUploadFile')->will($this->returnSelf());
        $this->uploader->expects($this->once())->method('save')->with($destDir . '/' . $fileName)
            ->willReturn(['name' => $fileName]);

        $this->uploader->setDestDir($destDir);
        $this->assertEquals(['name' => $fileName], $this->uploader->move($fileName));
    }

    /**
     * @return array
     */
    public function moveFileUrlDataProvider()
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

    /**
     * @dataProvider validatePathDataProvider
     *
     * @param bool $pathIsValid
     * @return void
     */
    public function testSetTmpDir($pathIsValid)
    {
        $path = 'path';
        $absolutePath = 'absolute_path';
        $this->directoryMock
            ->expects($this->atLeastOnce())
            ->method('isReadable')
            ->with($path)
            ->willReturn(true);
        $this->directoryMock
            ->expects($this->atLeastOnce())
            ->method('getAbsolutePath')
            ->with($path)
            ->willReturn($absolutePath);
        $this->directoryResolver
            ->expects($this->atLeastOnce())
            ->method('validatePath')
            ->with($absolutePath, 'base')
            ->willReturn($pathIsValid);

        $this->assertEquals($pathIsValid, $this->uploader->setTmpDir($path));
    }

    /**
     * Data provider for the testSetTmpDir()
     *
     * @return array
     */
    public function validatePathDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }
}
