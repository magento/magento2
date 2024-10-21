<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Parser;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

#[
    Config('path/to/config/class', 'ConfigTest')
]
class ConfigTest extends TestCase
{
    #[
        Config('path/to/config/method', 'testScopeMethod')
    ]
    public function testScopeMethod(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\Config();
        $this->assertEquals(
            [
                [
                    'path' => 'path/to/config/method',
                    'value' => 'testScopeMethod',
                    'scopeType' => 'default',
                    'scopeValue' => null
                ]
            ],
            $model->parse($this, ParserInterface::SCOPE_METHOD)
        );
    }

    #[
        Config('path/to/config/method', 'testScopeClass')
    ]
    public function testScopeClass(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\Config();
        $this->assertEquals(
            [
                [
                    'path' => 'path/to/config/class',
                    'value' => 'ConfigTest',
                    'scopeType' => 'default',
                    'scopeValue' => null
                ]
            ],
            $model->parse($this, ParserInterface::SCOPE_CLASS)
        );
    }
}
