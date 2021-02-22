<?php
/**
 * Test \Magento\Framework\Math\Random
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Math\Test\Unit;

class RandomTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param int    $length
     * @param string $chars
     *
     * @dataProvider getRandomStringDataProvider
     */
    public function testGetRandomString($length, $chars = null)
    {
        $mathRandom = new \Magento\Framework\Math\Random();
        $string = $mathRandom->getRandomString($length, $chars);

        $this->assertEquals($length, strlen($string));
        if ($chars !== null) {
            $this->_assertContainsOnlyChars($string, $chars);
        }
    }

    /**
     * @return array
     */
    public function getRandomStringDataProvider()
    {
        return [
            [0],
            [10],
            [10, \Magento\Framework\Math\Random::CHARS_LOWERS],
            [10, \Magento\Framework\Math\Random::CHARS_UPPERS],
            [10, \Magento\Framework\Math\Random::CHARS_DIGITS],
            [
                20,
                \Magento\Framework\Math\Random::CHARS_LOWERS .
                \Magento\Framework\Math\Random::CHARS_UPPERS .
                \Magento\Framework\Math\Random::CHARS_DIGITS
            ]
        ];
    }

    public function testGetUniqueHash()
    {
        $mathRandom = new \Magento\Framework\Math\Random();
        $hashOne = $mathRandom->getUniqueHash();
        $hashTwo = $mathRandom->getUniqueHash();
        $this->assertIsString($hashOne);
        $this->assertIsString($hashTwo);
        $this->assertNotEquals($hashOne, $hashTwo);
    }

    /**
     * @param string $string
     * @param string $chars
     */
    protected function _assertContainsOnlyChars($string, $chars)
    {
        if (preg_match('/[^' . $chars . ']+/', $string, $matches)) {
            $this->fail(sprintf('Unexpected char "%s" found', $matches[0]));
        }
    }

    /**
     * @param $min
     * @param $max
     *
     * @dataProvider testGetRandomNumberProvider
     */
    public function testGetRandomNumber($min, $max)
    {
        $number = \Magento\Framework\Math\Random::getRandomNumber($min, $max);
        $this->assertLessThanOrEqual($max, $number);
        $this->assertGreaterThanOrEqual($min, $number);
    }

    /**
     * @return array
     */
    public function testGetRandomNumberProvider()
    {
        return [
            [0, 100],
            [0, 1],
            [0, 0],
            [-1, 0],
            [-100, 0],
            [-1, 1],
            [-100, 100]
        ];
    }
}
