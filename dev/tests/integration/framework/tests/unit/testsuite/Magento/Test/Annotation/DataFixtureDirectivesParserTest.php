<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Annotation;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Annotation\DataFixtureDirectivesParser;
use PHPUnit\Framework\TestCase;

/**
 * Test data fixture directives parser service
 */
class DataFixtureDirectivesParserTest extends TestCase
{
    /**
     * @var DataFixtureDirectivesParser
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DataFixtureDirectivesParser(new Json());
    }

    /**
     * Test parse with different format
     *
     * @param string $directive
     * @dataProvider directivesDataProvider
     */
    public function testParse(string $directive, array $expected): void
    {
        $this->assertEquals($expected, $this->model->parse($directive));
    }

    /**
     * Test parse with invalid json
     */
    public function testParseInvalidJson(): void
    {
        $this->expectExceptionMessage('Unable to unserialize value. Error: Syntax error');
        $this->model->parse('path/to/fixture.php as:test1 with:{"k1": "v1" "k2": ["v21", "v22"]}');
    }

    /**
     * @return array
     */
    public function directivesDataProvider(): array
    {
        return [
            [
                'path/to/fixture.php as:test1 with:{"k1": "v1", "k2": ["v21", "v22"], "k3": {"k 31": "v 31"}}',
                [
                    'name' => 'test1',
                    'factory' => 'path/to/fixture.php',
                    'data' => [
                        'k1' => 'v1',
                        'k2' => ['v21', 'v22'],
                        'k3' => ['k 31' => 'v 31'],
                    ],
                ]
            ],
            [
                'path/to/fixture.php with:{"k1": "v1", "k2": ["v21", "v22"], "k3": {"k 31": "v 31"}} as:test1',
                [
                    'name' => 'test1',
                    'factory' => 'path/to/fixture.php',
                    'data' => [
                        'k1' => 'v1',
                        'k2' => ['v21', 'v22'],
                        'k3' => ['k 31' => 'v 31'],
                    ],
                ]
            ],
            [
                'path/to/fixture.php with:{"k1": "v1", "k2": ["v21", "v22"], "k3": {"k 31": "v 31"}}',
                [
                    'name' => null,
                    'factory' => 'path/to/fixture.php',
                    'data' => [
                        'k1' => 'v1',
                        'k2' => ['v21', 'v22'],
                        'k3' => ['k 31' => 'v 31'],
                    ],
                ]
            ],
            [
                'path/to/fixture.php as:test1',
                [
                    'name' => 'test1',
                    'factory' => 'path/to/fixture.php',
                    'data' => [],
                ]
            ],
        ];
    }
}
