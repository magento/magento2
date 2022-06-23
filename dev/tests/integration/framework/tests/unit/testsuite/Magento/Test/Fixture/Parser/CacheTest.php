<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Parser;

use Magento\TestFramework\Fixture\Cache;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

#[
    Cache('test_class', true)
]
class CacheTest extends TestCase
{
    #[
        Cache('test_method', false)
    ]
    public function testScopeMethod(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\Cache();
        $this->assertEquals(
            [['type' => 'test_method', 'status' => false]],
            $model->parse($this, ParserInterface::SCOPE_METHOD)
        );
    }

    #[
        Cache('test_method', false)
    ]
    public function testScopeClass(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\Cache();
        $this->assertEquals(
            [['type' => 'test_class', 'status' => true]],
            $model->parse($this, ParserInterface::SCOPE_CLASS)
        );
    }
}
