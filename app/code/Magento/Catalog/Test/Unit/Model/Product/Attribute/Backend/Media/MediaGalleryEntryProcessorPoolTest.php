<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend\Media;

class MediaGalleryEntryProcessorPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     * |\Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryProcessor
     */
    protected $imageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     * |\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryProcessor
     */
    protected $videoMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product */
    protected $productMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
    protected $attributeMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * |\Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryProcessorPool
     */
    protected $processorPool;

    public function setUp()
    {
        $this->imageMock =
            $this->getMock(
                '\Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageMediaGalleryEntryProcessor',
                [],
                [],
                '',
                false
            );

        $this->videoMock =
            $this->getMock(
                '\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoMediaGalleryEntryProcessor',
                [],
                [],
                '',
                false
            );

        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->attributeMock =
            $this->getMock('\Magento\Eav\Model\Entity\Attribute\AbstractAttribute', [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->processorPool = $objectManager->getObject(
            '\Magento\Catalog\Model\Product\Attribute\Backend\Media\MediaGalleryEntryProcessorPool',
            [
                'mediaGalleryEntryProcessorsCollection' => [$this->imageMock, $this->videoMock]
            ]
        );
    }

    public function testConstructException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $dataObjectMock = $this->getMock('\Magento\Framework\DataObject', [], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManager->getObject(
            '\Magento\Catalog\Model\Product\Attribute\Backend\Media\MediaGalleryEntryProcessorPool',
            [
                'mediaGalleryEntryProcessorsCollection' => [$dataObjectMock]
            ]
        );
    }

    public function testProcessBeforeLoad()
    {
        $this->processorPool->processBeforeLoad($this->productMock, $this->attributeMock);
    }

    public function testProcessAfterLoad()
    {
        $this->processorPool->processAfterLoad($this->productMock, $this->attributeMock);
    }

    public function testProcessBeforeSave()
    {
        $this->processorPool->processBeforeSave($this->productMock, $this->attributeMock);
    }

    public function testProcessAfterSave()
    {
        $this->processorPool->processAfterSave($this->productMock, $this->attributeMock);
    }

    public function testProcessBeforeDelete()
    {
        $this->processorPool->processBeforeDelete($this->productMock, $this->attributeMock);
    }

    public function testProcessAfterDelete()
    {
        $this->processorPool->processAfterDelete($this->productMock, $this->attributeMock);
    }
}
