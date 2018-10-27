<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter;

class TruncateFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider truncateDataProvider
     * @param string $expectedValue
     * @param string $expectedRemainder
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param bool $breakWords
     * @return void
     */
    public function testFilter(
        string $expectedValue,
        string $expectedRemainder,
        string $string,
        int $length = 5,
        string $etc = '...',
        bool $breakWords = true
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

    /**
     * @return array
     */
    public function truncateDataProvider() : array
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
                false,
            ],
        ];
    }
}
