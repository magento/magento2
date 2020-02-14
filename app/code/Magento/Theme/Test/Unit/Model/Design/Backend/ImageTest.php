<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Framework\UrlInterface;
use Magento\Theme\Model\Design\Backend\Image;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as IoFileSystem;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mediaDirectory;

    /** @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var Image */
    protected $imageBackend;

    /** @var IoFileSystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $ioFileSystem;

    /**
     * @var \Magento\Framework\File\Mime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mime;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    private $databaseHelper;

    public function setUp()
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

        $this->ioFileSystem = $this->getMockBuilder(\Magento\Framework\Filesystem\Io\File::class)
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

        $this->imageBackend = new Image(
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
            $this->ioFileSystem
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->imageBackend,
            'mime',
            $this->mime
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

    /**
     * @dataProvider beforeSaveInvalidDataProvider
     * @param string $imageName
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something is wrong with the file upload settings.
     */
    public function testBeforeSaveInvalidImage($imageName)
    {
        {
            $this->imageBackend->setScope('store');
            $this->imageBackend->setScopeId(1);
            $this->imageBackend->setValue(
                [
                    [
                        'url' => 'http://magento2.com/pub/media/tmp/image/' . $imageName,
                        'file' => $imageName,
                        'size' => 234234,
                    ]
                ]
            );
            $this->imageBackend->setFieldConfig(
                [
                    'upload_dir' => [
                        'value' => 'value',
                        'config' => 'system/filesystem/media',
                    ],
                ]
            );

            $this->imageBackend->beforeSave();
        }
    }

    /**
     * @return array
     */
    public function beforeSaveInvalidDataProvider()
    {
        return [
            'Invalid Extension' => ['file.invalid'],
            'Vulnerable file name' => ['../../../../../../../../etc/passwd'],
        ];
    }
}
