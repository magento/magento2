<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Parser;

use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

#[
    DbIsolation(true)
]
class DbIsolationTest extends TestCase
{
    #[
        DbIsolation(false)
    ]
    public function testScopeMethod(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\DbIsolation();
        $this->assertEquals(
            [['enabled' => false]],
            $model->parse($this, ParserInterface::SCOPE_METHOD)
        );
    }

    #[
        DbIsolation(false)
    ]
    public function testScopeClass(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\DbIsolation();
        $this->assertEquals(
            [['enabled' => true]],
            $model->parse($this, ParserInterface::SCOPE_CLASS)
        );
    }
}
