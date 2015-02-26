<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\Minified;

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
        $this->prepareAttemptToMinifyMock(false);
        $this->asset->method('getContext')->willReturn($this->baseUrl);
        $this->asset->expects($this->once())->method('getContent')->will($this->returnValue('content'));
        $this->adapter->expects($this->once())->method('minify')->with('content')->will($this->returnValue('mini'));
        $this->staticViewDir->expects($this->once())->method('writeFile')->with($this->anything(), 'mini');
        $this->assertEquals('test/admin.js', $this->model->getFilePath());
        $this->assertEquals('http://example.com/test/admin.js', $this->model->getUrl());
    }
}
