<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\ValueTransformer;

use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer\IntegerTransformer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
     */
    #[DataProvider('valuesDataProvider')]
    public function testIntegerTransform(string $value, ?int $expected): void
    {
        $this->assertEquals($expected, $this->model->transform($value));
    }

    /**
     * Values data provider
     *
     * @return array
     */
    public static function valuesDataProvider(): array
    {
        return [
            ['12345', 12345],
            ['3310042623',null]
        ];
    }
}
