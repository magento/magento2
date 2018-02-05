<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Model\Product\Attribute\Media;

/**
 * VideoEntry test
 */
class VideoEntryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ProductVideo\Model\Product\Attribute\Media\VideoEntry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelObject;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->modelObject =
            $this->getMock(
                '\Magento\ProductVideo\Model\Product\Attribute\Media\VideoEntry',
                ['getData', 'setData'],
                [],
                '',
                false
            );
    }

    /**
     * Test getMediaType()
     */
    public function testGetMediaType()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('image');
        $this->modelObject->getMediaType();
    }

    /**
     * Test setMediaType()
     */
    public function testSetMediaType()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setMediaType('image');
    }

    /**
     * Test getVideoProvider()
     */
    public function testGetVideoProvider()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('provider');
        $this->modelObject->getVideoProvider();
    }

    /**
     * Test setVideoProvider()
     */
    public function testSetVideoProvider()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoProvider('provider');
    }

    /**
     * Test getVideoUrl()
     */
    public function testGetVideoUrl()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn(
            'https://www.url.com/watch?v=aaaaaaaaa'
        );
        $this->modelObject->getVideoUrl();
    }

    /**
     * Test setVideoUrl()
     */
    public function testSetVideoUrl()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoUrl('https://www.url.com/watch?v=aaaaaaaaa');
    }

    /**
     * Test getVideoTitle()
     */
    public function testGetVideoTitle()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Title');
        $this->modelObject->getVideoTitle();
    }

    /**
     * Test setVideoTitle()
     */
    public function testSetVideoTitle()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoTitle('Title');
    }

    /**
     * Test getVideoDescription()
     */
    public function testGetVideoDescription()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Description');
        $this->modelObject->getVideoDescription();
    }

    /**
     * Test setVideoDescription()
     */
    public function testSetVideoDescription()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoDescription('Description');
    }

    /**
     * Test getVideoMetadata()
     */
    public function testGetVideoMetadata()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Meta data');
        $this->modelObject->getVideoMetadata();
    }

    /**
     * Test setVideoMetadata()
     */
    public function testSetVideoMetadata()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoMetadata('Meta data');
    }
}
