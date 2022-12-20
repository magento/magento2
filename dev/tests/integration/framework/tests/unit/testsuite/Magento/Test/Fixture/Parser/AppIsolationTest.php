<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Parser;

use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

#[
    AppIsolation(true)
]
class AppIsolationTest extends TestCase
{
    #[
        AppIsolation(false)
    ]
    public function testScopeMethod(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\AppIsolation();
        $this->assertEquals(
            [['enabled' => false]],
            $model->parse($this, ParserInterface::SCOPE_METHOD)
        );
    }

    #[
        AppIsolation(false)
    ]
    public function testScopeClass(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\AppIsolation();
        $this->assertEquals(
            [['enabled' => true]],
            $model->parse($this, ParserInterface::SCOPE_CLASS)
        );
    }
}
