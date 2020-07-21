<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntryConverterPoolTest extends TestCase
{
    /**
     * @var MockObject
     * |\Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter
     */
    protected $imageMock;

    /**
     * @var MockObject
     * |\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter
     */
    protected $videoMock;

    /**
     * @var MockObject|DataObject
     */
    protected $dataObjectMock;

    protected function setUp(): void
    {
        $this->imageMock =
            $this->createMock(ImageEntryConverter::class);

        $this->imageMock->expects($this->any())->method('getMediaEntryType')->willReturn('image');

        $this->videoMock =
            $this->createMock(ExternalVideoEntryConverter::class);

        $this->videoMock->expects($this->any())->method('getMediaEntryType')->willReturn('external-video');

        $this->dataObjectMock = $this->createMock(DataObject::class);
    }

    public function testGetConverterByMediaTypeImage()
    {
        $objectManager = new ObjectManager($this);

        $converterPool = $objectManager->getObject(
            EntryConverterPool::class,
            [
                'mediaGalleryEntryConvertersCollection' => [$this->imageMock, $this->videoMock]
            ]
        );

        $result = $converterPool->getConverterByMediaType('image');
        $this->assertNotNull($result);
    }

    public function testGetConverterByMediaTypeVideo()
    {
        $objectManager = new ObjectManager($this);

        $converterPool = $objectManager->getObject(
            EntryConverterPool::class,
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

        $objectManager = new ObjectManager($this);

        $converterPool = $objectManager->getObject(
            EntryConverterPool::class,
            [
                'mediaGalleryEntryConvertersCollection' => [$this->dataObjectMock]
            ]
        );

        $converterPool->getConverterByMediaType('external-video');
    }

    public function testGetConverterByMediaTypeImageException()
    {
        $objectManager = new ObjectManager($this);

        $converterPool = $objectManager->getObject(
            EntryConverterPool::class,
            [
                'mediaGalleryEntryConvertersCollection' => [$this->imageMock, $this->videoMock]
            ]
        );

        $this->expectException(LocalizedException::class);

        $converterPool->getConverterByMediaType('something_wrong');
    }
}
