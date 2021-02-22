<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Block\Product\View;

/**
 * Class GalleryTest
 */
class GalleryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $arrayUtilsMock;

    /**
     * @var \Magento\ProductVideo\Helper\Media|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mediaHelperMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * |\Magento\ProductVideo\Block\Adminhtml\Product\Video\Gallery
     */
    protected $gallery;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productModelMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(\Magento\Catalog\Block\Product\Context::class);
        $this->arrayUtilsMock = $this->createMock(\Magento\Framework\Stdlib\ArrayUtils::class);
        $this->mediaHelperMock = $this->createMock(\Magento\ProductVideo\Helper\Media::class);
        $this->jsonEncoderMock = $this->createMock(\Magento\Framework\Json\EncoderInterface::class);
        $this->coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $this->contextMock->expects($this->once())->method('getRegistry')->willReturn($this->coreRegistry);

        $this->productModelMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->gallery = $objectManager->getObject(
            \Magento\ProductVideo\Block\Product\View\Gallery::class,
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
        $mediaGalleryData = new \Magento\Framework\DataObject();
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
        $typeInstance = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
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
        $typeInstance = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
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
