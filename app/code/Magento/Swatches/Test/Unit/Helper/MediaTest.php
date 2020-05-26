<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Helper;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Config\View;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image;
use Magento\Framework\Image\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Swatches\Helper\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Helper to move images from tmp to catalog directory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MediaTest extends TestCase
{
    /** @var MockObject|Config */
    protected $mediaConfigMock;

    /** @var MockObject|Filesystem */
    protected $fileSystemMock;

    /** @var MockObject|WriteInterface */
    protected $writeInstanceMock;

    /** @var MockObject|Database */
    protected $fileStorageDbMock;

    /** @var MockObject|StoreManager */
    protected $storeManagerMock;

    /** @var MockObject|Factory */
    protected $imageFactoryMock;

    /** @var MockObject|\Magento\Framework\View\Config */
    protected $viewConfigMock;

    /** @var MockObject|Write */
    protected $mediaDirectoryMock;

    /** @var MockObject|Store */
    protected $storeMock;

    /** @var Media|ObjectManager */
    protected $mediaHelperObject;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->mediaConfigMock = $this->createMock(Config::class);
        $this->writeInstanceMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->fileStorageDbMock = $this->createPartialMock(
            Database::class,
            ['checkDbUsage', 'getUniqueFilename', 'renameFile']
        );

        $this->storeManagerMock = $this->createPartialMock(StoreManager::class, ['getStore']);

        $this->imageFactoryMock = $this->createMock(Factory::class);

        $this->viewConfigMock = $this->createMock(\Magento\Framework\View\Config::class);

        $this->storeMock = $this->createPartialMock(Store::class, ['getBaseUrl']);

        $this->mediaDirectoryMock = $this->createMock(Write::class);
        $this->fileSystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryWrite']);
        $this->fileSystemMock
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->mediaDirectoryMock);

        $this->mediaHelperObject = $objectManager->getObject(
            Media::class,
            [
                'mediaConfig' => $this->mediaConfigMock,
                'filesystem' => $this->fileSystemMock,
                'fileStorageDb' => $this->fileStorageDbMock,
                'storeManager' => $this->storeManagerMock,
                'imageFactory' => $this->imageFactoryMock,
                'configInterface' => $this->viewConfigMock,
            ]
        );
    }

    /**
     * @dataProvider dataForFullPath
     */
    public function testGetSwatchAttributeImage($swatchType, $expectedResult)
    {
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock
            ->expects($this->once())
            ->method('getBaseUrl')
            ->with('media')
            ->willReturn('http://url/pub/media/');

        $this->generateImageConfig();

        $this->testGenerateSwatchVariations();

        $result = $this->mediaHelperObject->getSwatchAttributeImage($swatchType, '/f/i/file.png');

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function dataForFullPath()
    {
        return [
            [
                'swatch_image',
                'http://url/pub/media/attribute/swatch/swatch_image/30x20/f/i/file.png',
            ],
            [
                'swatch_thumb',
                'http://url/pub/media/attribute/swatch/swatch_thumb/110x90/f/i/file.png',
            ],
        ];
    }

    public function testMoveImageFromTmp()
    {
        $this->fileStorageDbMock->method('checkDbUsage')->willReturn(1);
        $this->fileStorageDbMock->expects($this->atLeastOnce())->method('getUniqueFilename')->willReturn('file___1');
        $this->fileStorageDbMock->method('renameFile')->willReturnSelf();
        $this->mediaDirectoryMock->expects($this->exactly(2))->method('delete')->willReturnSelf();
        $this->mediaHelperObject->moveImageFromTmp('file.tmp');
    }

    public function testMoveImageFromTmpNoDb()
    {
        $this->fileStorageDbMock->method('checkDbUsage')->willReturn(false);
        $this->fileStorageDbMock->method('renameFile')->willReturnSelf();
        $result = $this->mediaHelperObject->moveImageFromTmp('file.tmp');
        $this->assertNotNull($result);
    }

    public function testGenerateSwatchVariations()
    {
        $this->mediaDirectoryMock
            ->expects($this->atLeastOnce())
            ->method('getAbsolutePath')
            ->willReturn('attribute/swatch/e/a/earth.png');

        $image = $this->createPartialMock(Image::class, [
            'resize',
            'save',
            'keepTransparency',
            'constrainOnly',
            'keepFrame',
            'keepAspectRatio',
            'backgroundColor',
            'quality'
        ]);

        $this->imageFactoryMock->expects($this->any())->method('create')->willReturn($image);
        $this->generateImageConfig();
        $image->expects($this->any())->method('resize')->willReturnSelf();
        $image->expects($this->atLeastOnce())->method('backgroundColor')->with([255, 255, 255])->willReturnSelf();
        $this->mediaHelperObject->generateSwatchVariations('/e/a/earth.png');
    }

    public function testGetSwatchMediaUrl()
    {
        $storeMock = $this->createPartialMock(Store::class, ['getBaseUrl']);

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $storeMock
            ->expects($this->once())
            ->method('getBaseUrl')
            ->with('media')
            ->willReturn('http://url/pub/media/');

        $result = $this->mediaHelperObject->getSwatchMediaUrl();

        $this->assertEquals($result, 'http://url/pub/media/attribute/swatch');
    }

    /**
     * @dataProvider dataForFolderName
     */
    public function testGetFolderNameSize($swatchType, $imageConfig, $expectedResult)
    {
        if ($imageConfig === null) {
            $this->generateImageConfig();
        }
        $result = $this->mediaHelperObject->getFolderNameSize($swatchType, $imageConfig);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function dataForFolderName()
    {
        return [
            [
                'swatch_image',
                [
                    'swatch_image' => [
                        'width' => 30,
                        'height' => 20,
                    ],
                    'swatch_thumb' => [
                        'width' => 110,
                        'height' => 90,
                    ],
                ],
                '30x20',
            ],
            [
                'swatch_thumb',
                [
                    'swatch_image' => [
                        'width' => 30,
                        'height' => 20,
                    ],
                    'swatch_thumb' => [
                        'width' => 110,
                        'height' => 90,
                    ],
                ],
                '110x90',
            ],
            [
                'swatch_thumb',
                null,
                '110x90',
            ],
        ];
    }

    public function testGetImageConfig()
    {
        $this->generateImageConfig();
        $this->mediaHelperObject->getImageConfig();
    }

    protected function generateImageConfig()
    {
        $configMock = $this->createMock(View::class);

        $this->viewConfigMock
            ->expects($this->atLeastOnce())
            ->method('getViewConfig')
            ->willReturn($configMock);

        $imageConfig = [
            'swatch_image' => [
                'width' => 30,
                'height' => 20,
            ],
            'swatch_thumb' => [
                'width' => 110,
                'height' => 90,
            ],
        ];

        $configMock->expects($this->any())->method('getMediaEntities')->willReturn($imageConfig);
    }

    public function testGetAttributeSwatchPath()
    {
        $result = $this->mediaHelperObject->getAttributeSwatchPath('/m/a/magento.png');
        $this->assertEquals($result, 'attribute/swatch/m/a/magento.png');
    }

    public function testGetSwatchMediaPath()
    {
        $this->assertEquals('attribute/swatch', $this->mediaHelperObject->getSwatchMediaPath());
    }

    /**
     * @dataProvider getSwatchTypes
     */
    public function testGetSwatchCachePath($swatchType, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->mediaHelperObject->getSwatchCachePath($swatchType));
    }

    /**
     * @return array
     */
    public function getSwatchTypes()
    {
        return [
            [
                'swatch_image',
                'attribute/swatch/swatch_image/',
            ],
            [
                'swatch_thumb',
                'attribute/swatch/swatch_thumb/',
            ],
        ];
    }
}
