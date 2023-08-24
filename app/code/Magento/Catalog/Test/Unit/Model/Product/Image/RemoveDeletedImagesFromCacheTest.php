<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Image;

use Magento\Catalog\Model\Product\Image\ConvertImageMiscParamsToReadableFormat;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\Product\Image\RemoveDeletedImagesFromCache;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Config\View;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\View\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test deleted images from the cache
 */
class RemoveDeletedImagesFromCacheTest extends TestCase
{
    /**
     * @var MockObject|RemoveDeletedImagesFromCache
     */
    protected RemoveDeletedImagesFromCache|MockObject $model;

    /**
     * @var ConfigInterface|MockObject
     */
    protected ConfigInterface|MockObject $presentationConfig;

    /**
     * @var EncryptorInterface|MockObject
     */
    protected EncryptorInterface|MockObject $encryptor;

    /**
     * @var Config|MockObject
     */
    protected Config|MockObject $mediaConfig;

    /**
     * @var MockObject|Write
     */
    protected Write|MockObject $mediaDirectory;

    /**
     * @var MockObject|ParamsBuilder
     */
    protected ParamsBuilder|MockObject $imageParamsBuilder;

    /**
     * @var ConvertImageMiscParamsToReadableFormat|MockObject
     */
    protected ConvertImageMiscParamsToReadableFormat|MockObject $convertImageMiscParamsToReadableFormat;

    /**
     * @var MockObject|View
     */
    protected View|MockObject $viewMock;

    protected function setUp(): void
    {
        $this->presentationConfig = $this->createMock(ConfigInterface::class);

        $this->encryptor = $this->createMock(EncryptorInterface::class);

        $this->mediaConfig = $this->createMock(Config::class);

        $this->mediaDirectory = $this->createMock(Write::class);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->mediaDirectory);

        $this->imageParamsBuilder = $this->createMock(ParamsBuilder::class);

        $this->convertImageMiscParamsToReadableFormat = $this
            ->createMock(ConvertImageMiscParamsToReadableFormat::class);

        $this->model = new RemoveDeletedImagesFromCache(
            $this->presentationConfig,
            $this->encryptor,
            $this->mediaConfig,
            $filesystem,
            $this->imageParamsBuilder,
            $this->convertImageMiscParamsToReadableFormat
        );

        $this->viewMock = $this->createMock(View::class);
    }

    /**
     * @param array $data
     * @return void
     * @dataProvider createDataProvider
     */
    public function testRemoveDeletedImagesFromCache(array $data): void
    {
        $this->getRespectiveMethodMockObjForRemoveDeletedImagesFromCache($data);

        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $this->model->removeDeletedImagesFromCache(['i/m/image.jpg']);
    }

    /**
     * @param array $data
     * @return void
     * @dataProvider createDataProvider
     */
    public function testRemoveDeletedImagesFromCacheWithException(array $data): void
    {
        $this->getRespectiveMethodMockObjForRemoveDeletedImagesFromCache($data);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Unable to write file into directory product/cache. Access forbidden.');

        $exception = new FileSystemException(
            new Phrase('Unable to write file into directory product/cache. Access forbidden.')
        );

        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->willThrowException($exception);

        $this->model->removeDeletedImagesFromCache(['i/m/image.jpg']);
    }

    /**
     * @return void
     */
    public function testRemoveDeletedImagesFromCacheWithEmptyFiles(): void
    {
        $this->assertEquals(
            null,
            $this->model->removeDeletedImagesFromCache([])
        );
    }

    /**
     * @param array $data
     * @return void
     */
    public function getRespectiveMethodMockObjForRemoveDeletedImagesFromCache(array $data): void
    {
        $this->presentationConfig->expects($this->once())
            ->method('getViewConfig')
            ->with(['area' => \Magento\Framework\App\Area::AREA_FRONTEND])
            ->willReturn($this->viewMock);

        $this->viewMock->expects($this->once())
            ->method('getMediaEntities')
            ->willReturn([$data['viewImageConfig']]);

        $this->imageParamsBuilder->expects($this->once())
            ->method('build')
            ->willReturn($data['imageParamsBuilder']);

        $this->convertImageMiscParamsToReadableFormat->expects($this->once())
            ->method('convertImageMiscParamsToReadableFormat')
            ->willReturn($data['convertImageParamsToReadableFormat']);

        $this->encryptor->expects($this->once())
            ->method('hash')
            ->willReturn('85b0304775df23c13f08dd2c1f9c4c28');

        $this->mediaConfig->expects($this->once())
            ->method('getBaseMediaPath')
            ->willReturn('catalog/product');
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            $this->getTestDataWithAttributes()
        ];
    }

    /**
     * @return array
     */
    private function getTestDataWithAttributes(): array
    {
        return [
            'data' => [
                'viewImageConfig' => [
                    'width' => 100,
                    'height' => 50,
                    'constrain_only' => false,
                    'aspect_ratio' => false,
                    'frame' => true,
                    'transparency' => false,
                    'background' => '255,255,255',
                    'type' => 'thumbnail' //thumbnail,small_image,image,swatch_image,swatch_thumb
                ],
                'imageParamsBuilder' => [
                    'image_width' => 100,
                    'image_height' => 50,
                    'constrain_only' => false,
                    'keep_aspect_ratio' => false,
                    'keep_frame' => true,
                    'keep_transparency' => false,
                    'background' => '255,255,255',
                    'image_type' => 'thumbnail', //thumbnail,small_image,image,swatch_image,swatch_thumb
                    'quality' => 80,
                    'angle' => null
                ],
                'convertImageParamsToReadableFormat' => [
                    'image_height' => 'h: 50',
                    'image_width' => 'w: 100',
                    'quality' => 'q: 80',
                    'angle' => 'r: ',
                    'keep_aspect_ratio' => 'non proportional',
                    'keep_frame' => 'no frame',
                    'keep_transparency' => 'no transparency',
                    'constrain_only' => 'not constrainonly',
                    'background' => 'rgb 255,255,255'
                ]
            ]
        ];
    }
}
