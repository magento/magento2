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
<<<<<<< HEAD
=======
     * @dataProvider truncateDataProvider
>>>>>>> upstream/2.2-develop
     * @param string $expectedValue
     * @param string $expectedRemainder
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param bool $breakWords
<<<<<<< HEAD
     * @dataProvider truncateDataProvider
     */
    public function testFilter(
        $expectedValue,
        $expectedRemainder,
        $string,
        $length = 5,
        $etc = '...',
        $breakWords = true
=======
     * @return void
     */
    public function testFilter(
        string $expectedValue,
        string $expectedRemainder,
        string $string,
        int $length = 5,
        string $etc = '...',
        bool $breakWords = true
>>>>>>> upstream/2.2-develop
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

<<<<<<< HEAD
=======
    /**
     * @return array
     */
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
                false
            ]
=======
                false,
            ],
>>>>>>> upstream/2.2-develop
        ];
    }
}
