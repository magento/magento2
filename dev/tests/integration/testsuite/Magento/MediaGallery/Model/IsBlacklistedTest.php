<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\MediaGalleryApi\Api\IsPathBlacklistedInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for IsPathBlacklistedInterface
 */
class IsBlacklistedTest extends TestCase
{

    /**
     * @var IsPathBlacklistedInterface
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->service = Bootstrap::getObjectManager()->get(IsPathBlacklistedInterface::class);
    }

    /**
     * Testing the blacklisted paths
     *
     * @param string $path
     * @param bool $isBlacklisted
     * @dataProvider pathsProvider
     */
    public function testExecute(string $path, bool $isBlacklisted): void
    {
        $this->assertEquals($isBlacklisted, $this->service->execute($path));
    }

    /**
     * Provider of paths and if the path should be in the blacklist
     *
     * @return array
     */
    public function pathsProvider(): array
    {
        return [
            ['theme', true],
            ['.thumbs', true],
            ['catalog/product/somedir', true],
            ['catalog/category', false]
        ];
    }
}
