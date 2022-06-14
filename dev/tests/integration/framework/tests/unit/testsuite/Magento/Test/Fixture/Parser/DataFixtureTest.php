<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Parser;

use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

#[
    DataFixture('\Test\Fixture\Test', ['param1' => 'value1'])
]
class DataFixtureTest extends TestCase
{
    #[
        DataFixture('\Test\Fixture\Test1', ['method' => 'testScopeMethod'], 'f1'),
        DataFixture('\Test\Fixture\Test2', as: 'f2'),
        DataFixture('\Test\Fixture\Test3')
    ]
    public function testScopeMethod(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\DataFixture();
        $this->assertEquals(
            [
                [
                    'name' => 'f1',
                    'factory' => '\Test\Fixture\Test1',
                    'data' => ['method' => 'testScopeMethod']
                ],
                [
                    'name' => 'f2',
                    'factory' => '\Test\Fixture\Test2',
                    'data' => []
                ],
                [
                    'name' => null,
                    'factory' => '\Test\Fixture\Test3',
                    'data' => []
                ]
            ],
            $model->parse($this, ParserInterface::SCOPE_METHOD)
        );
    }

    #[
        DataFixture('\Test\Fixture\Test1', ['method' => 'testScopeClass'])
    ]
    public function testScopeClass(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\DataFixture();
        $this->assertEquals(
            [
                [
                    'name' => null,
                    'factory' => '\Test\Fixture\Test',
                    'data' => ['param1' => 'value1']
                ]
            ],
            $model->parse($this, ParserInterface::SCOPE_CLASS)
        );
    }
}
