<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure;

use Magento\Config\Model\Config\Structure\AbstractMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AbstractMapperTest extends TestCase
{
    /**
     * @param string $key
     * @param mixed $target
     * @param bool $expected
     */
    #[DataProvider('hasValueDataProvider')]
    public function testHasValue(string $key, mixed $target, bool $expected): void
    {
        $mapper = new class extends AbstractMapper {
            public function hasValue(string $key, mixed $target): bool
            {
                return $this->_hasValue($key, $target);
            }

            public function map(array $data)
            {
                return [];
            }
        };

        $this->assertSame($expected, $mapper->hasValue($key, $target));
    }

    /**
     * @return array
     */
    public static function hasValueDataProvider(): array
    {
        return [
            'existing nested path' => ['depends/fields', ['depends' => ['fields' => ['a' => 1]]], true],
            'missing key' => ['depends/fields', ['children' => []], false],
            'null intermediate value' => ['depends/fields', ['depends' => null], false],
            'scalar intermediate value' => ['depends/fields', ['depends' => 'x'], false],
            'null leaf value' => ['depends', ['depends' => null], true],
            'non array target' => ['depends', null, false],
        ];
    }
}
