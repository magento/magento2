<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Model\Product\Attribute\Media;

/**
 * @codeCoverageIgnore
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
        $this->modelObject->getMediaType();
    }

    public function testSetVideoProvider()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setMediaType('vimeo');
    }

    public function testGetVideoUrl()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn(
            'https://www.youtube.com/watch?v=abcdefghij'
        );
        $this->modelObject->getMediaType();
    }

    public function testSetVideoUrl()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setMediaType('https://www.youtube.com/watch?v=abcdefghij');
    }

    public function testGetVideoTitle()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Title');
        $this->modelObject->getMediaType();
    }

    public function testSetVideoTitle()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setMediaType('Title');
    }

    public function testGetVideoDescription()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Description');
        $this->modelObject->getMediaType();
    }

    public function testSetVideoDescription()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setMediaType('Description');
    }

    public function testGetVideoMetadata()
    {
        $this->modelObject->expects($this->once())->method('getData')->willReturn('Meta data');
        $this->modelObject->getMediaType();
    }

    public function testSetVideoMetadata()
    {
        $this->modelObject->expects($this->once())->method('setData')->willReturn($this->modelObject);
        $this->modelObject->setMediaType('Meta data');
    }
}
