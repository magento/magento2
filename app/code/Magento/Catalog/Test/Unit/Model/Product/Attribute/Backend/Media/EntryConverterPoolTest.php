<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend\Media;

/**
 * Class aggregate all Media Gallery Entry Converters
 */
class EntryConverterPoolTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->imageMock =
            $this->getMock(
                '\Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter',
                [],
                [],
                '',
                false
            );

        $this->imageMock->expects($this->any())->method('getMediaEntryType')->willReturn('image');

        $this->videoMock =
            $this->getMock(
                '\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter',
                [],
                [],
                '',
                false
            );

        $this->videoMock->expects($this->any())->method('getMediaEntryType')->willReturn('external-video');

        $this->dataObjectMock = $this->getMock('\Magento\Framework\DataObject', [], [], '', false);
    }

    public function testGetConverterByMediaTypeImage()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $converterPool = $objectManager->getObject(
            '\Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool',
            [
                'mediaGalleryEntryConvertersCollection' => [$this->imageMock, $this->videoMock]
            ]
        );

        $converterPool->getConverterByMediaType('image');
    }

    public function testGetConverterByMediaTypeVideo()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $converterPool = $objectManager->getObject(
            '\Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool',
            [
                'mediaGalleryEntryConvertersCollection' => [$this->imageMock, $this->videoMock]
            ]
        );

        $converterPool->getConverterByMediaType('external-video');
    }

    public function testConstructException()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $converterPool = $objectManager->getObject(
            '\Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool',
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
            '\Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool',
            [
                'mediaGalleryEntryConvertersCollection' => [$this->imageMock, $this->videoMock]
            ]
        );

        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException');

        $converterPool->getConverterByMediaType('something_wrong');
    }
}
