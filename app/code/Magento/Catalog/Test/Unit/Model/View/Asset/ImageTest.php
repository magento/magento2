<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\View\Asset;

use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Catalog\Model\View\Asset\Image;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    /**
     * @var Image
     */
    protected $model;

    /**
     * @var ContextInterface|MockObject
     */
    protected $mediaConfig;

    /**
     * @var EncryptorInterface|MockObject
     */
    protected $encryptor;

    /**
     * @var ContextInterface|MockObject
     */
    protected $context;

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

    private $objectManager;

    protected function setUp(): void
    {
        $this->mediaConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->encryptor = $this->getMockForAbstractClass(EncryptorInterface::class);
        $this->context = $this->getMockForAbstractClass(ContextInterface::class);
        $this->assetRepo = $this->createMock(Repository::class);
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            Image::class,
            [
                'mediaConfig' => $this->mediaConfig,
                'imageContext' => $this->context,
                'encryptor' => $this->encryptor,
                'filePath' => '/somefile.png',
                'assetRepo' => $this->assetRepo,
                'miscParams' => [
                    'image_width' => 100,
                    'image_height' => 50,
                    'constrain_only' => false,
                    'keep_aspect_ratio' => false,
                    'keep_frame' => true,
                    'keep_transparency' => false,
                    'background' => '255,255,255',
                    'image_type' => 'image', //thumbnail,small_image,image,swatch_image,swatch_thumb
                    'quality' => 80,
                    'angle' => null
                ]
            ]
        );
    }

    public function testModuleAndContentAndContentType()
    {
        $contentType = 'image';
        $this->assertEquals($contentType, $this->model->getContentType());
        $this->assertEquals($contentType, $this->model->getSourceContentType());
        $this->assertNull($this->model->getContent());
        $this->assertEquals('cache', $this->model->getModule());
    }

    public function testGetFilePath()
    {
        $this->assertEquals('/somefile.png', $this->model->getFilePath());
    }

    public function testGetSoureFile()
    {
        $this->mediaConfig->expects($this->once())->method('getBaseMediaPath')->willReturn('catalog/product');
        $this->assertEquals('catalog/product/somefile.png', $this->model->getSourceFile());
    }

    public function testGetContext()
    {
        $this->assertInstanceOf(ContextInterface::class, $this->model->getContext());
    }

    /**
     * @param string $filePath
     * @param array $miscParams
     * @param string $readableParams
     * @dataProvider getPathDataProvider
     */
    public function testGetPath($filePath, $miscParams, $readableParams)
    {
        $imageModel = $this->objectManager->getObject(
            Image::class,
            [
                'mediaConfig' => $this->mediaConfig,
                'context' => $this->context,
                'encryptor' => $this->encryptor,
                'filePath' => $filePath,
                'assetRepo' => $this->assetRepo,
                'miscParams' => $miscParams
            ]
        );
        $absolutePath = '/var/www/html/magento2ce/pub/media/catalog/product';
        $hashPath = 'somehash';
        $this->context->method('getPath')->willReturn($absolutePath);
        $this->encryptor->expects(static::once())
            ->method('hash')
            ->with($readableParams, $this->anything())
            ->willReturn($hashPath);
        static::assertEquals(
            $absolutePath . '/cache/' . $hashPath . $filePath,
            $imageModel->getPath()
        );
    }

    /**
     * @param string $filePath
     * @param array $miscParams
     * @param string $readableParams
     * @dataProvider getPathDataProvider
     */
    public function testGetUrl($filePath, $miscParams, $readableParams)
    {
        $imageModel = $this->objectManager->getObject(
            Image::class,
            [
                'mediaConfig' => $this->mediaConfig,
                'context' => $this->context,
                'encryptor' => $this->encryptor,
                'filePath' => $filePath,
                'assetRepo' => $this->assetRepo,
                'miscParams' => $miscParams
            ]
        );
        $absolutePath = 'http://localhost/pub/media/catalog/product';
        $hashPath = 'somehash';
        $this->context->expects(static::once())->method('getBaseUrl')->willReturn($absolutePath);
        $this->encryptor->expects(static::once())
            ->method('hash')
            ->with($readableParams, $this->anything())
            ->willReturn($hashPath);
        static::assertEquals(
            $absolutePath . '/cache/' . $hashPath . $filePath,
            $imageModel->getUrl()
        );
    }

    /**
     * @return array
     */
    public function getPathDataProvider()
    {
        return [
            [
                '/some_file.png',
                [], //default value for miscParams,
                'h:empty_w:empty_q:empty_r:empty_nonproportional_noframe_notransparency_notconstrainonly_nobackground',
            ],
            [
                '/some_file_2.png',
                [
                    'image_type' => 'thumbnail',
                    'image_height' => 75,
                    'image_width' => 75,
                    'keep_aspect_ratio' => true,
                    'keep_frame' => true,
                    'keep_transparency' => true,
                    'constrain_only' => true,
                    'background' => [233,1,0],
                    'angle' => null,
                    'quality' => 80,
                ],
                'h:75_w:75_proportional_frame_transparency_doconstrainonly_rgb233,1,0_r:empty_q:80',
            ],
            [
                '/some_file_3.png',
                [
                    'image_type' => 'thumbnail',
                    'image_height' => 75,
                    'image_width' => 75,
                    'keep_aspect_ratio' => false,
                    'keep_frame' => false,
                    'keep_transparency' => false,
                    'constrain_only' => false,
                    'background' => [233,1,0],
                    'angle' => 90,
                    'quality' => 80,
                ],
                'h:75_w:75_nonproportional_noframe_notransparency_notconstrainonly_rgb233,1,0_r:90_q:80',
            ],
        ];
    }
}
