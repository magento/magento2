<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Block\Product\View\Gallery;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Gallery
     */
    protected $model;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var ArrayUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayUtils;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var ImagesConfigFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imagesConfigFactoryMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $galleryImagesConfigMock;

    /** @var  UrlBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $urlBuilder;

    protected function setUp()
    {
        $this->registry = $this->createMock(Registry::class);
        $this->context = $this->createConfiguredMock(
            Context::class,
            ['getRegistry' => $this->registry]
        );

        $this->arrayUtils = $this->createMock(ArrayUtils::class);
        $this->jsonEncoderMock = $this->createMock(EncoderInterface::class);
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

    public function testGetGalleryImages()
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
    private function getImagesConfigFactory()
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
    private function getGalleryImagesConfigItems()
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
}
