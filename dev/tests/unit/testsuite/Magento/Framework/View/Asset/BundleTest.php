<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle */
    protected $bundle;

    /** @var \PHPUnit_Framework_MockObject_MockObject|File */
    protected $asset;

    /** @var array */
    protected $assets = [];

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem */
    protected $filesystem;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle\ConfigInterface */
    protected $bundleConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\File\FallbackContext */
    protected $context;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Write */
    protected $directoryWrite;

    // @codingStandardsIgnoreStart
    /**
     * @var string
     */
    protected $expectedResult = <<<EOL
require.config({
    config: {
        'text':{"cf/cf.html":"Content","c4/c4.html":"Content","c8/c8.html":"Content","ec/ec.html":"Content","a8/a8.html":"Content"}
    }
});
require.config({
    config: {
        'jsbuild':{"e4/e4.js":"Content","16/16.js":"Content","8f/8f.js":"Content","c9/c9.js":"Content","45/45.js":"Content"}
    }
});
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

EOL;

    /**
     * @var string
     */
    protected $expectedFirstPart = <<<EOL
require.config({
    config: {
        'text':{"cf/cf.html":"Content","c4/c4.html":"Content","c8/c8.html":"Content","ec/ec.html":"Content","a8/a8.html":"Content"}
    }
});

EOL;

    /**
     * @var string
     */
    protected $expectedSecondPart = <<<EOL
require.config({
    config: {
        'jsbuild':{"e4/e4.js":"Content","16/16.js":"Content","8f/8f.js":"Content","c9/c9.js":"Content","45/45.js":"Content"}
    }
});
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

EOL;
    // @codingStandardsIgnoreEnd

    protected function setUp()
    {
        $this->asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->bundleConfig = $this->getMock('Magento\Framework\View\Asset\Bundle\ConfigInterface', [], [], '', false);
        $this->context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $this->directoryWrite = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->context
            ->expects($this->atLeastOnce())
            ->method('getAreaCode')
            ->willReturn('testArea');
        $this->context
            ->expects($this->atLeastOnce())
            ->method('getThemePath')
            ->willReturn('testTheme');
        $this->context
            ->expects($this->atLeastOnce())
            ->method('getLocaleCode')
            ->willReturn('testLocale');
    }

    protected function initBundle(array $contentTypes)
    {
        $this->bundle = new Bundle($this->filesystem, $this->bundleConfig);

        $contentType = array_pop($contentTypes);
        for ($i = 0; $i < 10; $i++) {
            $contentType = $i == 5 ? array_pop($contentTypes) : $contentType;
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
                ->expects($this->any())
                ->method('getContentType')
                ->willReturn($contentType);
            $assetMock
                ->expects($this->any())
                ->method('getContext')
                ->willReturn($this->context);

            $this->bundle->addAsset($assetMock);
            $assetKey = $assetMock->getModule() . '/' .$assetMock->getFilePath();
            $this->assets[$assetKey] = $assetMock;
        }
        return $this->bundle;
    }

    //public function testAddAssetAndFlushWithoutSplit()
    //{
    //    $this->context
    //        ->expects($this->atLeastOnce())
    //        ->method('getPath')
    //        ->willReturn('');
    //
    //    $this->bundleConfig
    //        ->expects($this->atLeastOnce())
    //        ->method('getPartSize')
    //        ->willReturn('0');
    //    $this->bundleConfig
    //        ->expects($this->atLeastOnce())
    //        ->method('isSplit')
    //        ->willReturn(false);
    //
    //    $this->directoryWrite
    //        ->expects($this->once())
    //        ->method('writeFile')
    //        ->with('/js/bundle/bundle0.js', $this->expectedResult)
    //        ->willReturn(true);
    //
    //    $this->filesystem
    //        ->expects($this->once())
    //        ->method('getDirectoryWrite')
    //        ->willReturn($this->directoryWrite);
    //
    //    $this->initBundle(['js', 'html']);
    //    $this->bundle->flush();
    //}

    public function testAddAssetAndFlushWithSplit()
    {
        $this->context
            ->expects($this->atLeastOnce())
            ->method('getPath')
            ->willReturn('');

        $this->bundleConfig
            ->expects($this->atLeastOnce())
            ->method('getPartSize')
            ->willReturn(0.035);
        $this->bundleConfig
            ->expects($this->atLeastOnce())
            ->method('isSplit')
            ->willReturn(true);

        $this->directoryWrite
            ->expects($this->at(0))
            ->method('writeFile')
            ->with('/js/bundle/bundle0.js', $this->expectedFirstPart)
            ->willReturn(true);
        $this->directoryWrite
            ->expects($this->at(1))
            ->method('writeFile')
            ->with('/js/bundle/bundle1.js', $this->expectedSecondPart)
            ->willReturn(true);

        $this->filesystem
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWrite);

        $this->initBundle(['js', 'html']);
        $this->bundle->flush();
    }
}
