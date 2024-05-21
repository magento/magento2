<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for IsPathExcludedInterface
 */
class IsExcludedTest extends TestCase
{
    /**
     * @var IsPathExcludedInterface
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->service = Bootstrap::getObjectManager()->get(IsPathExcludedInterface::class);
    }

    /**
     * Testing the excluded paths
     *
     * @param string $path
     * @param bool $isExcluded
     * @dataProvider pathsProvider
     */
    public function testExecute(string $path, bool $isExcluded): void
    {
        $this->assertEquals($isExcluded, $this->service->execute($path));
    }

    /**
     * Provider of paths and if the path should be in the excluded list
     *
     * @return array
     */
    public static function pathsProvider(): array
    {
        return [
            ['theme', true],
            ['.thumbs', true],
            ['catalog/product/somedir', true],
            ['catalog/category', false]
        ];
    }
}
