<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter;

use PHPUnit\Framework\Attributes\DataProvider;

class TruncateFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $expectedValue
     * @param string $expectedRemainder
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param bool $breakWords
     */
    #[DataProvider('truncateDataProvider')]
    public function testFilter(
        $expectedValue,
        $expectedRemainder,
        $string,
        $length = 5,
        $etc = '...',
        $breakWords = true
    ) {
        /** @var TruncateFilter $truncateFilter */
        $truncateFilter = \Magento\TestFramework\ObjectManager::getInstance()->create(
            TruncateFilter::class,
            [
                'length' => $length,
                'etc' => $etc,
                'breakWords' => $breakWords,
            ]
        );
        $result = $truncateFilter->filter($string);
        $this->assertEquals($expectedValue, $result->getValue());
        $this->assertEquals($expectedRemainder, $result->getRemainder());
    }

    public static function truncateDataProvider() : array
    {
        return [
            '1' => [
                '12...',
                '34567890',
                '1234567890',
            ],
            '2' => [
                '123..',
                ' 456 789',
                '123 456 789',
                8,
                '..',
                false
            ]
        ];
    }
}
