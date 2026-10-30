<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Model\Product\Attribute\FilterableAllowedInputTypes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FilterableAllowedInputTypesTest extends TestCase
{
    /**
     * @param mixed $frontendInput
     * @param bool $expected
     * @return void
     */
    #[DataProvider('isAllowedDataProvider')]
    public function testIsAllowed(mixed $frontendInput, bool $expected): void
    {
        $model = new FilterableAllowedInputTypes(['boolean', 'select', 'multiselect', 'price']);

        $this->assertSame($expected, $model->isAllowed($frontendInput));
    }

    /**
     * @return array
     */
    public static function isAllowedDataProvider(): array
    {
        return [
            'select' => ['select', true],
            'boolean' => ['boolean', true],
            'multiselect' => ['multiselect', true],
            'price' => ['price', true],
            'text' => ['text', false],
            'textarea' => ['textarea', false],
            'media_image' => ['media_image', false],
            'gallery' => ['gallery', false],
            'empty' => ['', false],
            'null' => [null, false],
        ];
    }
}
