<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\DocRootLocator;

class DocRootLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider isPubDataProvider
     *
     * @param string $path
     * @param bool $isExist
     * @param bool $result
     */
    public function testIsPub($path, $isExist, $result)
    {
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->expects($this->once())->method('getServer')->willReturn($path);
        $reader = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $reader->expects($this->any())->method('isExist')->willReturn($isExist);
        $readFactory = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $readFactory->expects($this->once())->method('create')->willReturn($reader);
        $model = new DocRootLocator($request, $readFactory);
        $this->assertSame($result, $model->isPub());
    }

    public function isPubDataProvider()
    {
        return [
            ['/some/path/to/root', false, false],
            ['/some/path/to/root', true, false],
            ['/some/path/to/pub', false, true],
            ['/some/path/to/pub', true, false],
        ];
    }
}
