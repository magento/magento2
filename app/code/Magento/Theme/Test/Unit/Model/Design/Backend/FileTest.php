<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Theme\Model\Design\Backend\File;
use Magento\Framework\App\Filesystem\DirectoryList;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mediaDirectory;

    /** @var \Magento\Theme\Model\Design\Config\FileUploader\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileConfig;

    /** @var File */
    protected $fileBackend;

    public function setUp()
    {
        $context = $this->getMockObject('Magento\Framework\Model\Context');
        $registry = $this->getMockObject('Magento\Framework\Registry');
        $config = $this->getMockObjectForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');
        $cacheTypeList = $this->getMockObjectForAbstractClass('Magento\Framework\App\Cache\TypeListInterface');
        $uploaderFactory = $this->getMockObject('Magento\MediaStorage\Model\File\UploaderFactory', ['create']);
        $requestData = $this->getMockObjectForAbstractClass(
            'Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface'
        );
        $filesystem = $this->getMockObject('Magento\Framework\Filesystem');
        $this->fileConfig = $this->getMockObject('Magento\Theme\Model\Design\Config\FileUploader\Config');

        $this->mediaDirectory = $this->getMockObjectForAbstractClass(
            'Magento\Framework\Filesystem\Directory\WriteInterface'
        );

        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);

        $this->fileBackend = new File(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $this->fileConfig
        );
    }

    public function tearDown()
    {
        unset($this->fileBackend);
    }

    /**
     * @param string $class
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockObjectForAbstractClass($class)
    {
        return  $this->getMockBuilder($class)
            ->getMockForAbstractClass();
    }

    public function testAfterLoad()
    {
        $value = '/filename.jpg';
        $this->fileBackend->setValue($value);
        $this->fileBackend->setFieldConfig(
            [
                'upload_dir' => [
                    'value' => 'value',
                    'config' => 'system/filesystem/media',
                ],
            ]
        );
        $this->mediaDirectory->expects($this->once())
            ->method('stat')
            ->with($value)
            ->willReturn(['size' => 234234]);
        $this->fileConfig->expects($this->once())
            ->method('getStoreMediaUrl')
            ->willReturn('http://magento2.com/pub/media');
        $this->fileBackend->afterLoad();
        $this->assertEquals(
            [
                [
                    'url' => 'http://magento2.com/pub/media' . $value,
                    'file' => $value,
                    'size' => 234234,
                    'exists' => true,
                ]
            ],
            $this->fileBackend->getValue()
        );
    }

    public function testBeforeSave()
    {
        $value = 'filename.jpg';
        $tmpMediaPath = 'tmp/image/' . $value;
        $this->fileBackend->setScope('store');
        $this->fileBackend->setScopeId(1);
        $this->fileBackend->setValue(
            [
                [
                    'url' => 'http://magento2.com/pub/media/tmp/image/' . $value,
                    'file' => $value,
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
        $this->fileConfig->expects($this->exactly(2))
            ->method('getTmpMediaPath')
            ->with($value)
            ->willReturn($tmpMediaPath);

        $this->mediaDirectory->expects($this->once())
            ->method('copyFile')
            ->with($tmpMediaPath, '/' . $value)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with($tmpMediaPath);

        $this->fileBackend->beforeSave();
        $this->assertEquals('filename.jpg', $this->fileBackend->getValue());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage header_logo_src does not contain field 'file'
     */
    public function testBeforeSaveWithoutFile()
    {
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
}
