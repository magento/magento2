<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\ValueTransformer;

use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer\IntegerTransformer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * Test value transformer
 */
class IntegerTransformerTest extends TestCase
{
    /**
     * @var IntegerTransformer
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
            IntegerTransformer::class
        );
    }

    /**
     * Test integer transform value
     * @param string $value
     * @param int|null $expected
     * @return void
     * @dataProvider valuesDataProvider
     */
    public function testIntegerTransform(string $value, ?int $expected): void
    {
        $this->assertEquals($expected, $this->model->transform($value));
    }

    /**
     * Values data provider
     *
     * @return array
     */
    public function valuesDataProvider(): array
    {
        return [
            ['12345', 12345],
            ['3310042623',null]
        ];
    }
}
