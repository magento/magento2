<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend\Media;

/**
 * Class aggregate all Media Gallery Entry Converters
 */
class EntryConverterPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     * |\Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter
     */
    protected $imageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     * |\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter
     */
    protected $videoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject
     */
    protected $dataObjectMock;

    protected function setUp()
    {
        $this->imageMock =
            $this->createMock(\Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter::class);

        $this->imageMock->expects($this->any())->method('getMediaEntryType')->willReturn('image');

        $this->videoMock =
            $this->createMock(\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter::class);

        $this->videoMock->expects($this->any())->method('getMediaEntryType')->willReturn('external-video');

        $this->dataObjectMock = $this->createMock(\Magento\Framework\DataObject::class);
    }

    public function testGetConverterByMediaTypeImage()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $converterPool = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool::class,
            [
                'mediaGalleryEntryConvertersCollection' => [$this->imageMock, $this->videoMock]
            ]
        );

        $result = $converterPool->getConverterByMediaType('image');
        $this->assertNotNull($result);
    }

    public function testGetConverterByMediaTypeVideo()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $converterPool = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool::class,
            [
                'mediaGalleryEntryConvertersCollection' => [$this->imageMock, $this->videoMock]
            ]
        );

        $result = $converterPool->getConverterByMediaType('external-video');
        $this->assertNotNull($result);
    }

    public function testConstructException()
    {
        $this->expectException('\InvalidArgumentException');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $converterPool = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool::class,
            [
                'mediaGalleryEntryConvertersCollection' => [$this->dataObjectMock]
            ]
        );

        $converterPool->getConverterByMediaType('external-video');
    }

    public function testGetConverterByMediaTypeImageException()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $converterPool = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool::class,
            [
                'mediaGalleryEntryConvertersCollection' => [$this->imageMock, $this->videoMock]
            ]
        );

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $converterPool->getConverterByMediaType('something_wrong');
    }
}
