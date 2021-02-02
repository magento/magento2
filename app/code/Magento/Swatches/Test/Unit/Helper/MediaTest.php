<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Helper;

/**
 * Helper to move images from tmp to catalog directory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MediaTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Media\Config */
    protected $mediaConfigMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem */
    protected $fileSystemMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface */
    protected $writeInstanceMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\MediaStorage\Helper\File\Storage\Database */
    protected $fileStorageDbMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Store\Model\StoreManager */
    protected $storeManagerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Image\Factory */
    protected $imageFactoryMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Config */
    protected $viewConfigMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\Write */
    protected $mediaDirectoryMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Store\Model\Store */
    protected $storeMock;

    /** @var \Magento\Swatches\Helper\Media|\Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $mediaHelperObject;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->mediaConfigMock = $this->createMock(\Magento\Catalog\Model\Product\Media\Config::class);
        $this->writeInstanceMock = $this->createMock(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $this->fileStorageDbMock = $this->createPartialMock(
            \Magento\MediaStorage\Helper\File\Storage\Database::class,
            ['checkDbUsage', 'getUniqueFilename', 'renameFile']
        );

        $this->storeManagerMock = $this->createPartialMock(\Magento\Store\Model\StoreManager::class, ['getStore']);

        $this->imageFactoryMock = $this->createMock(\Magento\Framework\Image\Factory::class);

        $this->viewConfigMock = $this->createMock(\Magento\Framework\View\Config::class);

        $this->storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getBaseUrl']);

        $this->mediaDirectoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $this->fileSystemMock = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite']);
        $this->fileSystemMock
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->mediaDirectoryMock);

        $this->mediaHelperObject = $objectManager->getObject(
            \Magento\Swatches\Helper\Media::class,
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

        $image = $this->createPartialMock(\Magento\Framework\Image::class, [
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
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getBaseUrl']);

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
        $configMock = $this->createMock(\Magento\Framework\Config\View::class);

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
