<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Asset;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(Minification::class),
]
class MinificationTest extends TestCase
{
    /**
     * @var Minification
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Bootstrap::getObjectManager()->get(Minification::class);
    }

    #[
        TestWith(['js', '/hugerte/']),
        TestWith(['css', '/hugerte/']),
    ]
    public function testGetExcludes(string $contentType, string $path): void
    {
        $excludes = $this->model->getExcludes($contentType);
        self::assertContains($path, $excludes);
    }
}
