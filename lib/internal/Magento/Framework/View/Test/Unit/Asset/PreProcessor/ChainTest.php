<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessor\Chain;

/**
 * Class ChainTest
 *
 * @package Magento\Framework\View\Asset\PreProcessor
 */
class ChainTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var Chain
     */
    private $object;

    protected function setUp()
    {
        $this->asset = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\LocalInterface::class);
        $this->asset->expects($this->once())->method('getContentType')->will($this->returnValue('assetType'));
        $this->object = new Chain($this->asset, 'origContent', 'origType', 'origPath');
    }

    public function testGetAsset()
    {
        $this->assertSame($this->asset, $this->object->getAsset());
    }

    public function testGettersAndSetters()
    {
        $this->assertSame('origType', $this->object->getOrigContentType());
        $this->assertSame('origType', $this->object->getContentType());
        $this->assertSame('origContent', $this->object->getOrigContent());
        $this->assertSame('origContent', $this->object->getContent());
        $this->assertSame('assetType', $this->object->getTargetContentType());

        $this->object->setContent('anotherContent');
        $this->object->setContentType('anotherType');

        $this->assertSame('origType', $this->object->getOrigContentType());
        $this->assertSame('anotherType', $this->object->getContentType());
        $this->assertSame('origContent', $this->object->getOrigContent());
        $this->assertSame('anotherContent', $this->object->getContent());
        $this->assertSame('assetType', $this->object->getTargetContentType());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The requested asset type was 'assetType', but ended up with 'type'
     */
    public function testAssertValid()
    {
        $this->object->setContentType('type');
        $this->object->assertValid();
    }

    /**
     * @param string $content
     * @param string $type
     * @param bool $expected
     * @dataProvider isChangedDataProvider
     */
    public function testIsChanged($content, $type, $expected)
    {
        $this->object->setContent($content);
        $this->object->setContentType($type);
        $this->assertSame($expected, $this->object->isChanged());
    }

    /**
     * @return array
     */
    public function isChangedDataProvider()
    {
        return [
            ['origContent', 'origType', false],
            ['anotherContent', 'origType', true],
            ['origContent', 'anotherType', true],
            ['anotherContent', 'anotherType', true],
        ];
    }
}
