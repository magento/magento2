<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Model\Product\Attribute\Media;

/**
 * VideoEntry test
 */
class VideoEntryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\ProductVideo\Model\Product\Attribute\Media\VideoEntry|\PHPUnit_Framework_MockObject_MockObject */
    protected $modelObject;

    public function setUp()
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

    public function testGetMediaType()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('image');
        $this->modelObject->getMediaType();
    }

    public function testSetMediaType()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setMediaType('image');
    }

    public function testGetVideoProvider()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('youtube');
        $this->modelObject->getVideoProvider();
    }

    public function testSetVideoProvider()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoProvider('vimeo');
    }

    public function testGetVideoUrl()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn(
            'https://www.youtube.com/watch?v=abcdefghij'
        );
        $this->modelObject->getVideoUrl();
    }

    public function testSetVideoUrl()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoUrl('https://www.youtube.com/watch?v=abcdefghij');
    }

    public function testGetVideoTitle()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Title');
        $this->modelObject->getVideoTitle();
    }

    public function testSetVideoTitle()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoTitle('Title');
    }

    public function testGetVideoDescription()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Description');
        $this->modelObject->getVideoDescription();
    }

    public function testSetVideoDescription()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoDescription('Description');
    }

    public function testGetVideoMetadata()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Meta data');
        $this->modelObject->getVideoMetadata();
    }

    public function testSetVideoMetadata()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setVideoMetadata('Meta data');
    }
}
