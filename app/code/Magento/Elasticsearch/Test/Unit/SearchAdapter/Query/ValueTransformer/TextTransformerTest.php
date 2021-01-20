<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\ValueTransformer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer\TextTransformer;

/**
 * Test value transformer
 */
class TextTransformerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TextTransformer
     */
    protected $model;

    /**
     * Setup method
     * @return void
     */
    protected function setUp(): void
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
     * @dataProvider valuesDataProvider
     */
    public function testTransform($value, $expected)
    {
        $result = $this->model->transform($value);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function valuesDataProvider()
    {
        return [
            ['Laptop^camera{microphone}', 'Laptop\^camera\{microphone\}'],
            ['Birthday 25-Pack w/ Greatest of All Time Cupcake', 'Birthday 25\-Pack w\/ Greatest of All Time Cupcake'],
            ['Retro vinyl record ~d123 *star', 'Retro vinyl record \~d123 \*star'],
        ];
    }
}
