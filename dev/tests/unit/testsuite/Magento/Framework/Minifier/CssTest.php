<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Minifier;

class CssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Css
     */
    protected $model;

    /**
     * @var \CSSmin|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cssMinifierMock;

    protected function setUp()
    {
        $this->cssMinifierMock = $this->getMock('CSSmin', ['run'], [], '', false);
        $this->model = new Css($this->cssMinifierMock);
    }

    /**
     * @dataProvider filenamesProvider
     */
    public function testProcess($filename, $callCount)
    {
        $assetMock = $this->getMock('\Magento\Framework\View\Asset\File', ['getFilePath'], [], '', false);
        $assetMock->method('getFilePath')->willReturn($filename);

        $chainMock = $this->getMock('\Magento\Framework\View\Asset\PreProcessor\Chain', ['getAsset'], [], '', false);
        $chainMock->method('getAsset')->willReturn($assetMock);

        $this->cssMinifierMock->expects($this->exactly($callCount))->method('run');

        $this->model->process($chainMock);
    }

    public function filenamesProvider()
    {
        return [
            ['test.css', 1],
            ['test.min.css', 0],
            ['test.min.less', 0],
            ['admin.css', 1],
            ['ad.min.css.css', 1]
        ];
    }
}
