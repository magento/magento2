<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Annotation\Parser;

use Magento\TestFramework\Annotation\Parser\DataFixture;
use PHPUnit\Framework\TestCase;

/**
 * Test data fixture annotations parser service
 *
 * @magentoDataFixture path/to/fixture0.php
 */
class DataFixtureTest extends TestCase
{
    /**
     * @var DataFixture
     */
    private DataFixture $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DataFixture('magentoDataFixture');
    }

    /**
     * Test parse correct format
     *
     * @magentoDataFixture path/to/fixture1.php
     * @magentoDataFixture path/to/fixture2.php
     */
    public function testParseCorrectFormat(): void
    {
        $this->assertEquals(
            [
                [
                    'name' => null,
                    'factory' => 'path/to/fixture1.php',
                    'data' => [],
                ],
                [
                    'name' => null,
                    'factory' => 'path/to/fixture2.php',
                    'data' => [],
                ]
            ],
            $this->model->parse($this, 'method')
        );
        $this->assertEquals(
            [
                [
                    'name' => null,
                    'factory' => 'path/to/fixture0.php',
                    'data' => [],
                ],
            ],
            $this->model->parse($this, 'class')
        );
    }

    /**
     * @magentoDataFixture path/to/fixture1.php something
     */
    public function testParseIncorrectFormat(): void
    {
        $this->expectExceptionMessage(
            'Data Fixture annotation expects only one argument: path/to/fixture1.php something'
        );
        $this->model->parse($this, 'method');
    }
}
