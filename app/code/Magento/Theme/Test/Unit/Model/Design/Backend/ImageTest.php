<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Theme\Model\Design\Backend\Image;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImageTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mediaDirectory;

    /** @var \Magento\Theme\Model\Design\Config\FileUploader\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $imageConfig;

    /** @var Image */
    protected $imageBackend;

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
        $this->imageConfig = $this->getMockObject('Magento\Theme\Model\Design\Config\FileUploader\Config');

        $this->mediaDirectory = $this->getMockObjectForAbstractClass(
            'Magento\Framework\Filesystem\Directory\WriteInterface'
        );

        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);

        $this->imageBackend = new Image(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $this->imageConfig
        );
    }

    public function tearDown()
    {
        unset($this->imageBackend);
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
        $value = 'store/1/filename.jpg';
        $this->imageBackend->setValue($value);
        $this->mediaDirectory->expects($this->once())
            ->method('stat')
            ->with('/image/' . $value)
            ->willReturn(['size' => 234234]);
        $this->imageConfig->expects($this->once())
            ->method('getStoreMediaUrl')
            ->willReturn('http://magento2.com/pub/media');
        $this->imageBackend->afterLoad();
        $this->assertEquals(
            [
                [
                    'url' => 'http://magento2.com/pub/media/image/' . $value,
                    'file' => $value,
                    'size' => 234234,
                    'exists' => true,
                ]
            ],
            $this->imageBackend->getValue()
        );
    }

    public function testBeforeSave()
    {
        $value = 'filename.jpg';
        $tmpMediaPath = 'tmp/image/' . $value;
        $this->imageBackend->setScope('store');
        $this->imageBackend->setScopeId(1);
        $this->imageBackend->setValue(
            [
                [
                    'url' => 'http://magento2.com/pub/media/tmp/image/' . $value,
                    'file' => $value,
                    'size' => 234234,
                ]
            ]
        );
        $this->imageConfig->expects($this->exactly(2))
            ->method('getTmpMediaPath')
            ->with($value)
            ->willReturn($tmpMediaPath);

        $this->mediaDirectory->expects($this->once())
            ->method('copyFile')
            ->with(
                $tmpMediaPath,
                'image/store/1/' . $value
            )
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with($tmpMediaPath);

        $this->imageBackend->beforeSave();
        $this->assertEquals('store/1/filename.jpg', $this->imageBackend->getValue());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage header_logo_src does not contain field 'file'
     */
    public function testBeforeSaveWithoutFile()
    {
        $this->imageBackend->setData(
            [
                'value' => [
                    'test' => ''
                ],
                'field_config' => [
                    'field' => 'header_logo_src'
                ],
            ]
        );
        $this->imageBackend->beforeSave();
    }

    public function testBeforeSaveWithExistingFile()
    {
        $value = 'filename.jpg';
        $this->imageBackend->setValue(
            [
                [
                    'url' => 'http://magento2.com/pub/media/tmp/image/' . $value,
                    'file' => $value,
                    'size' => 234234,
                    'exists' => true
                ]
            ]
        );
        $this->imageBackend->beforeSave();
        $this->assertEquals(
            $value,
            $this->imageBackend->getValue()
        );
    }
}
