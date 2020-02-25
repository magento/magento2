<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Theme\Model\Design\Backend\Image;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /** @var Image */
    private $imageBackend;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $context = $this->getMockObject(\Magento\Framework\Model\Context::class);
        $registry = $this->getMockObject(\Magento\Framework\Registry::class);
        $config = $this->getMockObject(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $cacheTypeList = $this->getMockObject(\Magento\Framework\App\Cache\TypeListInterface::class);
        $uploaderFactory = $this->getMockObject(\Magento\MediaStorage\Model\File\UploaderFactory::class);
        $requestData = $this->getMockObject(
            \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface::class
        );
        $filesystem = $this->getMockObject(\Magento\Framework\Filesystem::class);
        $urlBuilder = $this->getMockObject(\Magento\Framework\UrlInterface::class);
        $databaseHelper = $this->getMockObject(\Magento\MediaStorage\Helper\File\Storage\Database::class);
        $abstractResource = $this->getMockObject(\Magento\Framework\Model\ResourceModel\AbstractResource::class);
        $abstractDb = $this->getMockObject(\Magento\Framework\Data\Collection\AbstractDb::class);
        $ioFileSystem = $this->getMockObject(\Magento\Framework\Filesystem\Io\File::class);
        $this->imageBackend = new Image(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $urlBuilder,
            $abstractResource,
            $abstractDb,
            [],
            $databaseHelper,
            $ioFileSystem
        );
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        unset($this->imageBackend);
    }

    /**
     * @param string $class
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockObject(string $class, array $methods = []): PHPUnit_Framework_MockObject_MockObject
    {
        $builder =  $this->getMockBuilder($class)
            ->disableOriginalConstructor();
        if (count($methods)) {
            $builder->setMethods($methods);
        }
        return  $builder->getMock();
    }

    /**
     * Test for beforeSave method with invalid file extension.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something is wrong with the file upload settings.
     */
    public function testBeforeSaveWithInvalidExtensionFile()
    {
        $this->imageBackend->setData(
            [
                'value' => [
                    [
                        'file' => 'fileName.invalidExtension',
                    ]
                ],
            ]
        );
        $this->imageBackend->beforeSave();
    }
}
