<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Theme\Model\Design\Backend\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends TestCase
{
    /** @var WriteInterface|MockObject */
    protected $mediaDirectory;

    /** @var UrlInterface|MockObject */
    protected $urlBuilder;

    /** @var File */
    protected $fileBackend;

    /**
     * @var Mime|MockObject
     */
    private $mime;

    /**
     * @var Database|MockObject
     */
    private $databaseHelper;

    protected function setUp(): void
    {
        $context = $this->getMockObject(Context::class);
        $registry = $this->getMockObject(Registry::class);
        $config = $this->getMockObjectForAbstractClass(ScopeConfigInterface::class);
        $cacheTypeList = $this->getMockObjectForAbstractClass(TypeListInterface::class);
        $uploaderFactory = $this->getMockObject(\Magento\MediaStorage\Model\File\UploaderFactory::class, ['create']);
        $requestData = $this->getMockObjectForAbstractClass(
            RequestDataInterface::class
        );
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaDirectory = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();

        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $this->mime = $this->getMockBuilder(Mime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->databaseHelper = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $abstractResource = $this->getMockBuilder(AbstractResource::class)
            ->getMockForAbstractClass();

        $abstractDb = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
            $this->databaseHelper
        );

        $objectManager = new ObjectManager($this);
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
     * @return MockObject
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
     * @return MockObject
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
            ->with('value/' . $value)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with('value/' . $value)
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
            ->with('value/' . $value)
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
            'Vulnerable file name' => ['../../../../../../../../etc/passwd'],
        ];
    }

    public function testBeforeSaveWithoutFile()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
