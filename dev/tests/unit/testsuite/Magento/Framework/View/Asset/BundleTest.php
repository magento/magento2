<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config */
    protected $scopeConf;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle\Config */
    protected $conf;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle */
    protected $bundle;

    protected $expectedResult = <<<EOL
require.config({
    config: {
        'jsbuild':{"cf/cf":"Content","c4/c4":"Content","c8/c8":"Content","ec/ec":"Content","a8/a8":"Content","e4/e4":"Content","16/16":"Content","8f/8f":"Content","c9/c9":"Content","45/45":"Content"}
    }
});

EOL;

    protected function setUp()
    {
        $this->scopeConf = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false
        );
        $this->conf = $this->getMockForAbstractClass(
            'Magento\Framework\View\Asset\Bundle\ConfigInterface',
            [],
            '',
            false
        );
        $this->asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
    }

    protected function getBundle()
    {
        $bundle = $this->bundle = new Bundle(
            $this->conf,
            $this->scopeConf
        );

        $bundle->setType('js');

        for ($i = 0; $i < 10; $i++) {
            $assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
            $assetMock
                ->expects($this->any())
                ->method('getModule')
                ->willReturn(substr(md5($i), 0, 2));
            $assetMock
                ->expects($this->any())
                ->method('getFilePath')
                ->willReturn(substr(md5($i), 0, 2));
            $assetMock
                ->expects($this->any())
                ->method('getContent')
                ->willReturn('Content');

            $bundle->addAsset($assetMock);
        }
        return $bundle;
    }

    public function testGetContent()
    {
        $this->scopeConf
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(1);

        $actual = $this->getBundle()->getContent();

        $this->assertInternalType('array', $actual);

        $this->assertArrayHasKey(0, $actual);
        $this->assertEquals($this->expectedResult, $actual[0]);
    }

    public function testGetContentWithMultipleBundleParts()
    {
        $this->scopeConf
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(2);

        $actual = $this->getBundle()->getContent();

        $this->assertInternalType('array', $actual);

        $this->assertArrayHasKey(0, $actual);
        $this->assertArrayHasKey(1, $actual);
    }
}
