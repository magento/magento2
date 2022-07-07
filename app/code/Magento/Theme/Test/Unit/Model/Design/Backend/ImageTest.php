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
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Theme\Model\Design\Backend\Image;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /** @var Image */
    private $imageBackend;

    /** @var File */
    private $ioFileSystem;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $context = $this->getMockObject(Context::class);
        $registry = $this->getMockObject(Registry::class);
        $config = $this->getMockObject(ScopeConfigInterface::class);
        $cacheTypeList = $this->getMockObject(TypeListInterface::class);
        $uploaderFactory = $this->getMockObject(UploaderFactory::class);
        $requestData = $this->getMockObject(RequestDataInterface::class);
        $filesystem = $this->getMockObject(Filesystem::class);
        $urlBuilder = $this->getMockObject(UrlInterface::class);
        $databaseHelper = $this->getMockObject(Database::class);
        $abstractResource = $this->getMockObject(AbstractResource::class);
        $abstractDb = $this->getMockObject(AbstractDb::class);
        $this->ioFileSystem = $this->getMockObject(File::class);
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
            $this->ioFileSystem
        );
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        unset($this->imageBackend);
    }

    /**
     * @param string $class
     * @param array $methods
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockObject(string $class, array $methods = []): \PHPUnit\Framework\MockObject\MockObject
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
     */
    public function testBeforeSaveWithInvalidExtensionFile()
    {
        $this->expectException(
            \Magento\Framework\Exception\LocalizedException::class
        );
        $this->expectExceptionMessage(
            'Something is wrong with the file upload settings.'
        );

        $invalidFileName = 'fileName.invalidExtension';
        $this->imageBackend->setData(
            [
                'value' => [
                    [
                        'file' => $invalidFileName,
                    ]
                ],
            ]
        );
        $expectedPathInfo = [
            'extension' => 'invalidExtension'
        ];
        $this->ioFileSystem
            ->expects($this->any())
            ->method('getPathInfo')
            ->with($invalidFileName)
            ->willReturn($expectedPathInfo);
        $this->imageBackend->beforeSave();
    }
}
