<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\UrlInterface;
use Magento\Theme\Model\Design\Backend\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as IoFile;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $mediaDirectory;

    /** @var UrlInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlBuilder;

    /** @var File */
    protected $fileBackend;

    /**
     * @var \Magento\Framework\File\Mime|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mime;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit\Framework\MockObject\MockObject
     */
    private $databaseHelper;

    /**
     * @var IoFile|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ioFileMock;

    /**
     * @var ReadFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tmpDirectory;

    protected function setUp(): void
    {
        $context = $this->getMockObject(\Magento\Framework\Model\Context::class);
        $registry = $this->getMockObject(\Magento\Framework\Registry::class);
        $config = $this->getMockObjectForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $cacheTypeList = $this->getMockObjectForAbstractClass(\Magento\Framework\App\Cache\TypeListInterface::class);
        $uploaderFactory = $this->getMockObject(\Magento\MediaStorage\Model\File\UploaderFactory::class, ['create']);
        $requestData = $this->getMockObjectForAbstractClass(
            \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface::class
        );
        $filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaDirectory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->getMockForAbstractClass();

        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();

        $this->mime = $this->getMockBuilder(\Magento\Framework\File\Mime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->databaseHelper = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $abstractResource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->getMockForAbstractClass();

        $abstractDb = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->ioFileMock = $this->getMockBuilder(IoFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tmpDirectory = $this->getMockBuilder(ReadFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'getRelativePath', 'getAbsolutePath'])
            ->getMock();

        $this->fileBackend = new File(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $this->urlBuilder,
            $abstractResource,
            $abstractDb,
            [],
            $this->databaseHelper,
            $this->ioFileMock,
            $this->tmpDirectory
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->fileBackend,
            'mime',
            $this->mime
        );
    }

    protected function tearDown(): void
    {
        unset($this->fileBackend);
    }

    /**
     * @param string $class
     * @param array $methods
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockObject($class, $methods = [])
    {
        $builder =  $this->getMockBuilder($class)
            ->disableOriginalConstructor();
        if (count($methods)) {
            $builder->setMethods($methods);
        }
        return  $builder->getMock();
    }

    /**
     * @param string $class
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockObjectForAbstractClass($class)
    {
        return  $this->getMockBuilder($class)
            ->getMockForAbstractClass();
    }

    public function testAfterLoad()
    {
        $value = 'filename.jpg';
        $mime = 'image/jpg';

        $absoluteFilePath = '/absolute_path/' . $value;

        $this->fileBackend->setValue($value);
        $this->fileBackend->setFieldConfig(
            [
                'upload_dir' => [
                    'value' => 'value',
                    'config' => 'system/filesystem/media',
                ],
                'base_url' => [
                    'type' => 'media',
                    'value' => 'design/file'
                ],
            ]
        );

        $this->mediaDirectory->expects($this->once())
            ->method('isExist')
            ->with($absoluteFilePath)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn($absoluteFilePath);

        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => UrlInterface::URL_TYPE_MEDIA])
            ->willReturn('http://magento2.com/pub/media/');
        $this->mediaDirectory->expects($this->once())
            ->method('getRelativePath')
            ->with('value')
            ->willReturn('value');
        $this->mediaDirectory->expects($this->once())
            ->method('stat')
            ->with($absoluteFilePath)
            ->willReturn(['size' => 234234]);

        $this->mime->expects($this->once())
            ->method('getMimeType')
            ->with($absoluteFilePath)
            ->willReturn($mime);

        $this->fileBackend->afterLoad();
        $this->assertEquals(
            [
                [
                    'url' => 'http://magento2.com/pub/media/design/file/' . $value,
                    'file' => $value,
                    'size' => 234234,
                    'exists' => true,
                    'name' => $value,
                    'type' => $mime,
                ]
            ],
            $this->fileBackend->getValue()
        );
    }

    /**
     * @dataProvider beforeSaveDataProvider
     * @param string $fileName
     */
    public function testBeforeSave($fileName)
    {
        $expectedFileName = basename($fileName);
        $expectedTmpMediaPath = 'tmp/design/file/' . $expectedFileName;
        $this->fileBackend->setScope('store');
        $this->fileBackend->setScopeId(1);
        $this->fileBackend->setValue(
            [
                [
                    'url' => 'http://magento2.com/pub/media/tmp/image/' . $fileName,
                    'file' => $fileName,
                    'size' => 234234,
                ]
            ]
        );
        $this->fileBackend->setFieldConfig(
            [
                'upload_dir' => [
                    'value' => 'value',
                    'config' => 'system/filesystem/media',
                ],
            ]
        );

        $this->tmpDirectory->method('create')->willReturn($this->tmpDirectory);
        $this->tmpDirectory->method('getRelativePath')->willReturn('design/file/' . $fileName);
        $this->tmpDirectory->method('getAbsolutePath')->willReturn('tmp/design/file/' . $fileName);

        $this->mediaDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn('/' . $fileName);

        $this->databaseHelper->expects($this->once())
            ->method('renameFile')
            ->with($expectedTmpMediaPath, '/' . $expectedFileName)
            ->willReturn(true);

        $this->mediaDirectory->expects($this->once())
            ->method('copyFile')
            ->with($expectedTmpMediaPath, '/' . $expectedFileName)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with($expectedTmpMediaPath);

        $this->fileBackend->beforeSave();
        $this->assertEquals($expectedFileName, $this->fileBackend->getValue());
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            'Normal file name' => ['filename.jpg'],
        ];
    }

    /**
     */
    public function testBeforeSaveWithoutFile()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('header_logo_src does not contain field \'file\'');

        $this->fileBackend->setData(
            [
                'value' => [
                    'test' => ''
                ],
                'field_config' => [
                    'field' => 'header_logo_src'
                ],
            ]
        );
        $this->fileBackend->beforeSave();
    }

    public function testBeforeSaveWithExistingFile()
    {
        $value = 'filename.jpg';
        $this->fileBackend->setValue(
            [
                [
                    'url' => 'http://magento2.com/pub/media/tmp/image/' . $value,
                    'file' => $value,
                    'size' => 234234,
                    'exists' => true
                ]
            ]
        );
        $this->fileBackend->setOrigData('value', $value);
        $this->fileBackend->beforeSave();
        $this->assertEquals(
            $value,
            $this->fileBackend->getValue()
        );
    }

    /**
     * Test for getRelativeMediaPath method.
     *
     * @param string $path
     * @param string $filename
     * @dataProvider getRelativeMediaPathDataProvider
     */
    public function testGetRelativeMediaPath(string $path, string $filename)
    {
        $reflection = new \ReflectionClass($this->fileBackend);
        $method = $reflection->getMethod('getRelativeMediaPath');
        $method->setAccessible(true);
        $this->assertEquals(
            $filename,
            $method->invoke($this->fileBackend, $path . $filename)
        );
    }

    /**
     * Data provider for testGetRelativeMediaPath.
     *
     * @return array
     */
    public function getRelativeMediaPathDataProvider(): array
    {
        return [
            'Normal path' => ['pub/media/', 'filename.jpg'],
            'Complex path' => ['somepath/pub/media/', 'filename.jpg'],
        ];
    }
}
