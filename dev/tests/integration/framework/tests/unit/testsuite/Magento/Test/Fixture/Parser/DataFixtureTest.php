<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
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
        DataFixture('\Test\Fixture\Test3'),
        DataFixture('\Test\Fixture\Test4', scope: 'store2'),
        DataFixture('\Test\Fixture\Test5', scope: 'website2', scopeType: 'website'),
    ]
    public function testScopeMethod(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\DataFixture();
        $this->assertEquals(
            [
                [
                    'name' => 'f1',
                    'factory' => '\Test\Fixture\Test1',
                    'data' => ['method' => 'testScopeMethod'],
                    'scope' => null,
                    'scopeType' => 'store',
                ],
                [
                    'name' => 'f2',
                    'factory' => '\Test\Fixture\Test2',
                    'data' => [],
                    'scope' => null,
                    'scopeType' => 'store',
                ],
                [
                    'name' => null,
                    'factory' => '\Test\Fixture\Test3',
                    'data' => [],
                    'scope' => null,
                    'scopeType' => 'store',
                ],
                [
                    'name' => null,
                    'factory' => '\Test\Fixture\Test4',
                    'data' => [],
                    'scope' => 'store2',
                    'scopeType' => 'store',
                ],
                [
                    'name' => null,
                    'factory' => '\Test\Fixture\Test5',
                    'data' => [],
                    'scope' => 'website2',
                    'scopeType' => 'website',
                ],
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
                    'data' => ['param1' => 'value1'],
                    'scope' => null,
                    'scopeType' => 'store',
                ]
            ],
            $model->parse($this, ParserInterface::SCOPE_CLASS)
        );
    }
}
