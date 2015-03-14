<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\Minified;

use Magento\Framework\View\Asset\Minified\ImmutablePathAsset;
use Magento\Framework\Object;

class ImmutablePathAssetTest extends AbstractAssetTestCase
{
    /**
     * @var ImmutablePathAsset
     */
    protected $model;

    protected function setUp()
    {
        parent::setUp();

        $this->model = new ImmutablePathAsset(
            $this->asset,
            $this->logger,
            $this->filesystem,
            $this->baseUrl,
            $this->adapter
        );
    }

    public function testImmutableFilePath()
    {
        $this->asset->expects($this->atLeastOnce())->method('getPath')->will($this->returnValue('test/admin.js'));
        $this->asset->expects($this->atLeastOnce())->method('getFilePath')->will($this->returnValue('test/admin.js'));
        $this->asset->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->will($this->returnValue('/foo/bar/test/admin.js'));
        if (true) {
            $this->rootDir->expects($this->once())
                ->method('getRelativePath')
                ->with('/foo/bar/test/admin.min.js')
                ->will($this->returnValue('test/admin.min.js'));
            $this->rootDir->expects($this->once())
                ->method('isExist')
                ->with('test/admin.min.js')
                ->will($this->returnValue(false));
        }
        $this->baseUrl->expects($this->once())->method('getBaseUrl')->will($this->returnValue('http://example.com/'));
        $this->staticViewDir
            ->expects($this->exactly(2-intval(true)))
            ->method('isExist')
            ->will($this->returnValue(false));

        $this->asset->method('getContext')->willReturn($this->baseUrl);
        $this->asset->expects($this->once())->method('getContent')->will($this->returnValue('content'));
        $this->adapter->expects($this->once())->method('minify')->with('content')->will($this->returnValue('mini'));
        $this->staticViewDir->expects($this->once())->method('writeFile')->with($this->anything(), 'mini');
        $this->assertEquals('test/admin.js', $this->model->getFilePath());
        $this->assertEquals('http://example.com/test/admin.js', $this->model->getUrl());
    }
}
