<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Framework\Filesystem\Directory\Writer| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

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
            ->setMethods(['writeFile', 'getRelativePath', 'isWritable', 'getAbsolutePath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite'])
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
            ])
            ->setMethods(['_setUploadFile', 'save', 'getTmpDir'])
            ->getMock();
    }

    /**
     * @dataProvider moveFileUrlDataProvider
     */
    public function testMoveFileUrl($fileUrl, $expectedHost, $expectedFileName)
    {
        $destDir = 'var/dest/dir';
        $expectedRelativeFilePath = $this->uploader->getTmpDir() . '/' . $expectedFileName;
        $this->directoryMock->expects($this->once())->method('isWritable')->with($destDir)->willReturn(true);
        $this->directoryMock->expects($this->any())->method('getRelativePath')->with($expectedRelativeFilePath);
        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($destDir)
            ->willReturn($destDir . '/' . $expectedFileName);
        // Check writeFile() method invoking.
        $this->directoryMock->expects($this->any())->method('writeFile')->will($this->returnValue($expectedFileName));

        // Create adjusted reader which does not validate path.
        $readMock = $this->getMockBuilder(\Magento\Framework\Filesystem\File\Read::class)
            ->disableOriginalConstructor()
            ->setMethods(['readAll'])
            ->getMock();
        // Check readAll() method invoking.
        $readMock->expects($this->once())->method('readAll')->will($this->returnValue(null));

        // Check create() method invoking with expected argument.
        $this->readFactory->expects($this->once())
                        ->method('create')
                        ->will($this->returnValue($readMock))->with($expectedHost);
        //Check invoking of getTmpDir(), _setUploadFile(), save() methods.
        $this->uploader->expects($this->any())->method('getTmpDir')->will($this->returnValue(''));
        $this->uploader->expects($this->once())->method('_setUploadFile')->will($this->returnSelf());
        $this->uploader->expects($this->once())->method('save')->with($destDir . '/' . $expectedFileName)
            ->willReturn(['name' => $expectedFileName]);

        $this->uploader->setDestDir($destDir);
        $this->assertEquals(['name' => $expectedFileName], $this->uploader->move($fileUrl));
    }

    public function testMoveFileName()
    {
        $destDir = 'var/dest/dir';
        $fileName = 'test_uploader_file';
        $expectedRelativeFilePath = $this->uploader->getTmpDir() . '/' . $fileName;
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
     * @dataProvider moveFileUrlDriverPoolDataProvider
     */
    public function testMoveFileUrlDrivePool($fileUrl, $expectedHost, $expectedDriverPool, $expectedScheme)
    {

        $driverPool = $this->getMock('Magento\Framework\Filesystem\DriverPool', ['getDriver']);
        $driverMock = $this->getMock($expectedDriverPool, ['readAll']);
        $driverMock->expects($this->any())->method('isExists')->willReturn(true);
        $driverMock->expects($this->any())->method('readAll')->willReturn(null);
        $driverPool->expects($this->any())->method('getDriver')->willReturn($driverMock);

        $readFactory = $this->getMockBuilder('Magento\Framework\Filesystem\File\ReadFactory')
            ->setConstructorArgs(
                [
                    $driverPool,
                ]
            )
            ->setMethods(['create'])
            ->getMock();

        $readFactory->expects($this->any())->method('create')->with($expectedHost, $expectedScheme)->willReturn($driverMock);


        $uploaderMock = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Uploader')
            ->setConstructorArgs([
                $this->coreFileStorageDb,
                $this->coreFileStorage,
                $this->imageFactory,
                $this->validator,
                $this->filesystem,
                $readFactory,
            ])
            ->getMock();


        $uploaderMock->move($fileUrl);
    }

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

    public function moveFileUrlDataProvider()
    {
        return [
            [
                '$fileUrl' => 'http://test_uploader_file',
                '$expectedHost' => 'test_uploader_file',
                '$expectedFileName' => 'httptest_uploader_file',
            ],
            [
                '$fileUrl' => 'https://!:^&`;file',
                '$expectedHost' => '!:^&`;file',
                '$expectedFileName' => 'httpsfile',
            ],
        ];
    }
}
