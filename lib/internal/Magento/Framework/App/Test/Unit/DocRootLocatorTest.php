<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\DocRootLocator;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Framework\App\DocRootLocator class.
 */
class DocRootLocatorTest extends TestCase
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
        $request = $this->createMock(Http::class);
        $request->expects($this->once())->method('getServer')->willReturn($path);

        $readFactory = $this->createMock(ReadFactory::class);

        $reader = $this->createMock(Read::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($reader);
        $reader->expects($this->any())->method('isExist')->willReturn($isExist);

        $model = new DocRootLocator($request, $readFactory, $filesystem);
        $this->assertSame($result, $model->isPub());
    }

    /**
     * @return array
     */
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
