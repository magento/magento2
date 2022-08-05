<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryTest extends TestCase
{
    /**
     * @var Gallery
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var ArrayUtils|MockObject
     */
    protected $arrayUtils;

    /**
     * @var Image|MockObject
     */
    protected $imageHelper;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var ImagesConfigFactoryInterface|MockObject
     */
    protected $imagesConfigFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $galleryImagesConfigMock;

    /**
     * @var  UrlBuilder|MockObject
     */
    private $urlBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->registry = $this->createMock(Registry::class);
        $this->context = $this->createConfiguredMock(
            Context::class,
            ['getRegistry' => $this->registry]
        );

        $this->arrayUtils = $this->createMock(ArrayUtils::class);
        $this->jsonEncoderMock = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->imagesConfigFactoryMock = $this->getImagesConfigFactory();
        $this->urlBuilder = $this->createMock(UrlBuilder::class);

        $objectManager = new ObjectManager($this);
        $this->model =  $objectManager->getObject(Gallery::class, [
            'context' => $this->context,
            'arrayUtils' => $this->arrayUtils,
            'jsonEncoder' => $this->jsonEncoderMock,
            'urlBuilder' => $this->urlBuilder,
            'imagesConfigFactory' => $this->imagesConfigFactoryMock
        ]);
    }

    /**
     * @return void
     */
    public function testGetGalleryImagesJsonWithLabel(): void
    {
        $this->prepareGetGalleryImagesJsonMocks();
        $json = $this->model->getGalleryImagesJson();
        $decodedJson = json_decode($json, true);
        $this->assertEquals('product_page_image_small_url', $decodedJson[0]['thumb']);
        $this->assertEquals('product_page_image_medium_url', $decodedJson[0]['img']);
        $this->assertEquals('product_page_image_large_url', $decodedJson[0]['full']);
        $this->assertEquals('test_label', $decodedJson[0]['caption']);
        $this->assertEquals('2', $decodedJson[0]['position']);
        $this->assertFalse($decodedJson[0]['isMain']);
        $this->assertEquals('test_media_type', $decodedJson[0]['type']);
        $this->assertEquals('test_video_url', $decodedJson[0]['videoUrl']);
    }

    /**
     * @return void
     */
    public function testGetGalleryImagesJsonWithoutLabel(): void
    {
        $this->prepareGetGalleryImagesJsonMocks(false);
        $json = $this->model->getGalleryImagesJson();
        $decodedJson = json_decode($json, true);
        $this->assertEquals('test_product_name', $decodedJson[0]['caption']);
    }

    /**
     * @return void
     */
    private function prepareGetGalleryImagesJsonMocks($hasLabel = true): void
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeMock = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock->expects($this->any())
            ->method('getStoreFilter')
            ->with($productMock)
            ->willReturn($storeMock);

        $productMock->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);
        $productMock->expects($this->any())
            ->method('getMediaGalleryImages')
            ->willReturn($this->getImagesCollectionWithPopulatedDataObject($hasLabel));
        $productMock->expects($this->any())
            ->method('getName')
            ->willReturn('test_product_name');

        $this->registry->expects($this->any())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);

        $this->imageHelper = $this->getMockBuilder(Image::class)
            ->onlyMethods(['init', 'setImageFile', 'getUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageHelper->expects($this->any())
            ->method('init')
            ->willReturnMap([
                [$productMock, 'product_page_image_small', [], $this->imageHelper],
                [$productMock, 'product_page_image_medium_no_frame', [], $this->imageHelper],
                [$productMock, 'product_page_image_large_no_frame', [], $this->imageHelper],
            ])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('setImageFile')
            ->with('test_file')
            ->willReturnSelf();
        $this->urlBuilder
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls(
                'product_page_image_small_url',
                'product_page_image_medium_url',
                'product_page_image_large_url'
            );

        $this->galleryImagesConfigMock->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn($this->getGalleryImagesConfigItems());
    }

    /**
     * @return void
     */
    public function testGetGalleryImages(): void
    {
        $productMock = $this->createMock(Product::class);
        $productTypeMock = $this->createMock(AbstractType::class);
        $productTypeMock->expects(static::once())
            ->method('getStoreFilter')
            ->with($productMock)
            ->willReturn($this->createMock(Store::class));

        $imagesCollection = $this->createConfiguredMock(
            Collection::class,
            ['getIterator' => new \ArrayIterator([new DataObject(['file' => 'test_file'])])]
        );

        $productMock->method('getTypeInstance')->willReturn($productTypeMock);
        $productMock->method('getMediaGalleryImages')->willReturn($imagesCollection);
        $this->registry->expects(static::once())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);
        $this->galleryImagesConfigMock->expects(static::exactly(1))
            ->method('getItems')
            ->willReturn($this->getGalleryImagesConfigItems());

        $images = $this->model->getGalleryImages();
        static::assertInstanceOf(Collection::class, $images);
    }

    /**
     * getImagesConfigFactory
     *
     * @return ImagesConfigFactoryInterface
     */
    private function getImagesConfigFactory(): ImagesConfigFactoryInterface
    {
        $this->galleryImagesConfigMock = $this->createConfiguredMock(
            Collection::class,
            ['getIterator' => new \ArrayIterator($this->getGalleryImagesConfigItems())]
        );
        $galleryImagesConfigFactoryMock = $this->createConfiguredMock(
            ImagesConfigFactoryInterface::class,
            ['create' => $this->galleryImagesConfigMock]
        );

        return $galleryImagesConfigFactoryMock;
    }

    /**
     * getGalleryImagesConfigItems
     *
     * @return array
     */
    private function getGalleryImagesConfigItems(): array
    {
        return  [
            new DataObject([
                'image_id' => 'product_page_image_small',
                'data_object_key' => 'small_image_url',
                'json_object_key' => 'thumb'
            ]),
            new DataObject([
                'image_id' => 'product_page_image_medium',
                'data_object_key' => 'medium_image_url',
                'json_object_key' => 'img'
            ]),
            new DataObject([
                'image_id' => 'product_page_image_large',
                'data_object_key' => 'large_image_url',
                'json_object_key' => 'full'
            ])
        ];
    }

    /**
     * @return Collection
     */
    private function getImagesCollectionWithPopulatedDataObject($hasLabel): Collection
    {
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [
            new DataObject([
                'file' => 'test_file',
                'label' => ($hasLabel ? 'test_label' : ''),
                'position' => '2',
                'media_type' => 'external-test_media_type',
                "video_url" => 'test_video_url'
            ]),
        ];

        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        return $collectionMock;
    }
}
