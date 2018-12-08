<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\View\Asset;

use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Catalog\Model\View\Asset\Image;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ImageTest
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\View\Asset\Image
     */
    protected $model;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaConfig;

    /**
     * @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptor;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    private $objectManager;

    protected function setUp()
    {
        $this->mediaConfig = $this->createMock(ConfigInterface::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);
        $this->context = $this->createMock(ContextInterface::class);
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
     * @dataProvider getPathDataProvider
     */
    public function testGetPath($filePath, $miscParams)
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
        $miscParams['background'] = isset($miscParams['background']) ? implode(',', $miscParams['background']) : '';
        $absolutePath = '/var/www/html/magento2ce/pub/media/catalog/product';
        $hashPath = md5(implode('_', $miscParams));
        $this->context->method('getPath')->willReturn($absolutePath);
        $this->encryptor->method('hash')->willReturn($hashPath);
        static::assertEquals(
            $absolutePath . '/cache/'. $hashPath . $filePath,
            $imageModel->getPath()
        );
    }

    /**
     * @param string $filePath
     * @param array $miscParams
     * @dataProvider getPathDataProvider
     */
    public function testGetUrl($filePath, $miscParams)
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
        $miscParams['background'] = isset($miscParams['background']) ? implode(',', $miscParams['background']) : '';
        $absolutePath = 'http://localhost/pub/media/catalog/product';
        $hashPath = md5(implode('_', $miscParams));
        $this->context->expects(static::once())->method('getBaseUrl')->willReturn($absolutePath);
        $this->encryptor->expects(static::once())->method('hash')->willReturn($hashPath);
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
                [], //default value for miscParams
            ],
            [
                '/some_file_2.png',
                [
                    'image_type' => 'thumbnail',
                    'image_height' => 75,
                    'image_width' => 75,
                    'keep_aspect_ratio' => 'proportional',
                    'keep_frame' => 'frame',
                    'keep_transparency' => 'transparency',
                    'constrain_only' => 'doconstrainonly',
                    'background' => [233,1,0],
                    'angle' => null,
                    'quality' => 80,
                ],
            ]
        ];
    }
}
