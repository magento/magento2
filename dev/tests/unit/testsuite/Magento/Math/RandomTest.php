<?php
/**
 * Test \Magento\Math\Random
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Math;

class RandomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int $length
     * @param string $chars
     *
     * @dataProvider getRandomStringDataProvider
     */
    public function testGetRandomString($length, $chars = null)
    {
        $mathRandom = new \Magento\Math\Random();
        $string = $mathRandom->getRandomString($length, $chars);

        $this->assertEquals($length, strlen($string));
        if ($chars !== null) {
            $this->_assertContainsOnlyChars($string, $chars);
        }
    }

    public function getRandomStringDataProvider()
    {
        return array(
            array(0),
            array(10),
            array(10, \Magento\Math\Random::CHARS_LOWERS),
            array(10, \Magento\Math\Random::CHARS_UPPERS),
            array(10, \Magento\Math\Random::CHARS_DIGITS),
            array(
                20,
                \Magento\Math\Random::CHARS_LOWERS .
                \Magento\Math\Random::CHARS_UPPERS .
                \Magento\Math\Random::CHARS_DIGITS
            )
        );
    }

    public function testGetUniqueHash()
    {
        $mathRandom = new \Magento\Math\Random();
        $hashOne = $mathRandom->getUniqueHash();
        $hashTwo = $mathRandom->getUniqueHash();
        $this->assertTrue(is_string($hashOne));
        $this->assertTrue(is_string($hashTwo));
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
}
