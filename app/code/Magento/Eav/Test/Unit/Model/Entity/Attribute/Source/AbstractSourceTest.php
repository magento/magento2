<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AbstractSourceTest extends TestCase
{
    /**
     * @param array $options
     * @param string|int $value
     * @param string|bool $expected
     * @return void
     */
    #[DataProvider('optionTextDataProvider')]
    public function testGetOptionText(array $options, string|int $value, string|bool $expected): void
    {
        $source = new class($options) extends AbstractSource {
            /**
             * @param array $options
             */
            public function __construct(private array $options)
            {
            }

            /**
             * @inheritDoc
             */
            public function getAllOptions()
            {
                return $this->options;
            }
        };

        $this->assertSame($expected, $source->getOptionText($value));
    }

    /**
     * @return array
     */
    public static function optionTextDataProvider(): array
    {
        $list = [
            ['value' => 1, 'label' => 'Option B'],
            ['value' => 2, 'label' => 'Option C'],
        ];

        return [
            'matching value' => [$list, 2, 'Option C'],
            'missing value 0 does not return the first option array' => [$list, 0, false],
            'missing value 1 string' => [[['value' => 5, 'label' => 'A']], '0', false],
            'flat map' => [[3 => 'Three'], 3, 'Three'],
        ];
    }
}
