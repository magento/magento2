<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SimpleDataObjectConverterTest extends TestCase
{
    /**
     * @var SimpleDataObjectConverter
     */
    private $simpleDataObjectConverter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleDataObjectConverter = Bootstrap::getObjectManager()->get(SimpleDataObjectConverter::class);
    }

    /**
     * Test snake case to camel case conversion and vice versa.
     *
     * @return void
     */
    public function testCaseConversion(): void
    {
        $snakeCaseToCamelCase = [
            'first_a_second' => 'firstASecond',
            'first_at_second' => 'firstAtSecond',
            'first_a_t_m_second' => 'firstATMSecond',
        ];

        foreach ($snakeCaseToCamelCase as $snakeCase => $camelCase) {
            $this->assertEquals(
                $camelCase,
                $this->simpleDataObjectConverter->snakeCaseToCamelCase($snakeCase)
            );
            $this->assertEquals(
                $snakeCase,
                $this->simpleDataObjectConverter->camelCaseToSnakeCase($camelCase)
            );
        }
    }
}
