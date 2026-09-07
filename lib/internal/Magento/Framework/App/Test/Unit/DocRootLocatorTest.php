<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\DocRootLocator;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Framework\App\DocRootLocator class.
 */
class DocRootLocatorTest extends TestCase
{
    /**
     *
     * @param string $path
     * @param bool $isExist
     * @param bool $result
     */
    #[DataProvider('isPubDataProvider')]
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
    public static function isPubDataProvider()
    {
        return [
            ['/some/path/to/root', false, false],
            ['/some/path/to/root', true, false],
            ['/some/path/to/pub', false, true],
            ['/some/path/to/pub', true, false],
        ];
    }
}
