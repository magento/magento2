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

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle */
    protected $bundle;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Bundle\ResolverInterface */
    protected $resolver;

    /** @var \PHPUnit_Framework_MockObject_MockObject|File */
    protected $asset;

    /** @var array */
    protected $assetSet = [];

    // @codingStandardsIgnoreStart
    /**
     * @var string
     */
    protected $expectedResult = <<<EOL
require.config({
    bundles: {
        'mage/requirejs/static': [
            'jsbuild',
            'buildTools',
            'text',
            'statistician'
        ]
    },
    deps: [
        'jsbuild'
    ]
});
require.config({
    config: {
        'jsbuild':{"cf/cf.js.js":"Content","c4/c4.js.js":"Content","c8/c8.js.js":"Content","ec/ec.js.js":"Content","a8/a8.js.js":"Content","e4/e4.js.js":"Content","16/16.js.js":"Content","8f/8f.js.js":"Content","c9/c9.js.js":"Content","45/45.js.js":"Content"}
    }
});

EOL;
    /**
     * @var string
     */
    protected $expectedHtmlTypeResult = <<<EOL
require.config({
    bundles: {
        'mage/requirejs/static': [
            'jsbuild',
            'buildTools',
            'text',
            'statistician'
        ]
    },
    deps: [
        'jsbuild'
    ]
});
require.config({
    config: {
        'text':{"cf/cf":"Content","c4/c4":"Content","c8/c8":"Content","ec/ec":"Content","a8/a8":"Content","e4/e4":"Content","16/16":"Content","8f/8f":"Content","c9/c9":"Content","45/45":"Content"}
    }
});

EOL;
    // @codingStandardsIgnoreEnd

    protected function setUp()
    {
        $this->scopeConf = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false
        );
        $this->asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $this->resolver = $this->getMock('Magento\Framework\View\Asset\Bundle\ResolverInterface', [], [], '', false);
    }

    protected function getBundle($contentType)
    {
        $bundle = $this->bundle = new Bundle($this->scopeConf, $this->resolver);

        for ($i = 0; $i < 10; $i++) {
            $assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
            $assetMock
                ->expects($this->any())
                ->method('getModule')
                ->willReturn(substr(md5($i), 0, 2));
            $assetMock
                ->expects($this->any())
                ->method('getFilePath')
                ->willReturn(substr(md5($i), 0, 2) . '.' . $contentType);
            $assetMock
                ->expects($this->any())
                ->method('getContent')
                ->willReturn('Content');
            $assetMock
                ->expects($this->once())
                ->method('getContentType')
                ->willReturn($contentType);

            $bundle->addAsset($assetMock);
            $assetKey = $assetMock->getModule() . '/' .$assetMock->getFilePath();
            $this->assetSet[$assetKey] = $assetMock;
        }
        return $bundle;
    }

    public function testGetContentWithoutHtmlAndWithoutDividing()
    {
        $bundle = $this->getBundle('js');
        $resolvedAssets = [];
        foreach ($this->assetSet as $asset) {
            $assetKey = $asset->getModule() . '/' .$asset->getFilePath() . '.js';
            $resolvedAssets[$assetKey] = $asset->getContent();
        }
        $this->resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->assetSet)
            ->willReturn([$resolvedAssets]);
        $this->resolver
            ->expects($this->once())
            ->method('appendHtmlPart')
            ->with([$this->expectedResult, []])
            ->willReturn($this->expectedResult);

        $result = $bundle->getContent();
        $this->assertEquals($this->expectedResult, $result);
    }
}
