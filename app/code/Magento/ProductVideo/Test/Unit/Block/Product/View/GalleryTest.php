<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductVideo\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductVideo\Block\Product\View\Gallery;
use Magento\ProductVideo\Helper\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GalleryTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ArrayUtils|MockObject
     */
    protected $arrayUtilsMock;

    /**
     * @var Media|MockObject
     */
    protected $mediaHelperMock;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var Registry|MockObject
     */
    protected $coreRegistry;

    /**
     * @var ObjectManager
     * |\Magento\ProductVideo\Block\Adminhtml\Product\Video\Gallery
     */
    protected $gallery;

    /**
     * @var Product|MockObject
     */
    protected $productModelMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->arrayUtilsMock = $this->createMock(ArrayUtils::class);
        $this->mediaHelperMock = $this->createMock(Media::class);
        $this->jsonEncoderMock = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->coreRegistry = $this->createMock(Registry::class);
        $this->contextMock->expects($this->once())->method('getRegistry')->willReturn($this->coreRegistry);

        $this->productModelMock = $this->createMock(Product::class);

        $objectManager = new ObjectManager($this);

        $this->gallery = $objectManager->getObject(
            Gallery::class,
            [
                'context' => $this->contextMock,
                'arrayUtils' => $this->arrayUtilsMock,
                'mediaHelper' => $this->mediaHelperMock,
                'jsonEncoder' => $this->jsonEncoderMock,
            ]
        );
    }

    /**
     * Test getMediaGalleryDataJson()
     */
    public function testGetMediaGalleryDataJson()
    {
        $mediaGalleryData = new DataObject();
        $data = [
            [
                'media_type' => 'external-video',
                'video_url' => 'http://magento.ce/pub/media/catalog/product/9/b/9br6ujuthnc.jpg',
                'is_base' => true,
            ],
            [
                'media_type' => 'external-video',
                'video_url' => 'https://www.youtube.com/watch?v=QRYX7GIvdLE',
                'is_base' => false,
            ],
            [
                'media_type' => '',
                'video_url' => '',
                'is_base' => null,
            ]
        ];
        $mediaGalleryData->setData($data);

        $this->coreRegistry->expects($this->any())->method('registry')->willReturn($this->productModelMock);
        $typeInstance = $this->createMock(AbstractType::class);
        $typeInstance->expects($this->any())->method('getStoreFilter')->willReturn('_cache_instance_store_filter');
        $this->productModelMock->expects($this->any())->method('getTypeInstance')->willReturn($typeInstance);
        $this->productModelMock->expects($this->any())->method('getMediaGalleryImages')->willReturn(
            [$mediaGalleryData]
        );
        $this->gallery->getMediaGalleryDataJson();
    }

    /**
     * Test getMediaEmptyGalleryDataJson()
     */
    public function testGetMediaEmptyGalleryDataJson()
    {
        $mediaGalleryData = [];
        $this->coreRegistry->expects($this->any())->method('registry')->willReturn($this->productModelMock);
        $typeInstance = $this->createMock(AbstractType::class);
        $typeInstance->expects($this->any())->method('getStoreFilter')->willReturn('_cache_instance_store_filter');
        $this->productModelMock->expects($this->any())->method('getTypeInstance')->willReturn($typeInstance);
        $this->productModelMock->expects($this->any())->method('getMediaGalleryImages')->willReturn($mediaGalleryData);
        $this->gallery->getMediaGalleryDataJson();
    }

    /**
     * Test getVideoSettingsJson
     */
    public function testGetVideoSettingsJson()
    {
        $this->mediaHelperMock->expects($this->once())->method('getPlayIfBaseAttribute')->willReturn(1);
        $this->mediaHelperMock->expects($this->once())->method('getShowRelatedAttribute')->willReturn(0);
        $this->mediaHelperMock->expects($this->once())->method('getVideoAutoRestartAttribute')->willReturn(0);
        $this->gallery->getVideoSettingsJson();
    }
}
