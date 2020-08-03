<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\ValueTransformer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer\TextTransformer;
use PHPUnit\Framework\TestCase;

/**
 * Test value transformer
 */
class TextTransformerTest extends TestCase
{
    /**
     * @var TextTransformer
     */
    protected $model;

    /**
     * Setup method
     * @return void
     */
    public function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            TextTransformer::class,
            [
                '$preprocessors' => [],
            ]
        );
    }

    /**
     * Test transform value
     *
     * @param string $value
     * @param string $expected
     * @return void
     * @dataProvider valuesDataProvider
     */
    public function testTransform(string $value, string $expected): void
    {
        $result = $this->model->transform($value);
        $this->assertEquals($expected, $result);
    }

    /**
     * Values data provider
     *
     * @return array
     */
    public function valuesDataProvider(): array
    {
        return [
            ['Laptop^camera{microphone}', 'Laptop\^camera\{microphone\}'],
            ['Birthday 25-Pack w/ Greatest of All Time Cupcake', 'Birthday 25\-Pack w\/ Greatest of All Time Cupcake'],
            ['Retro vinyl record ~d123 *star', 'Retro vinyl record \~d123 \*star'],
        ];
    }
}
