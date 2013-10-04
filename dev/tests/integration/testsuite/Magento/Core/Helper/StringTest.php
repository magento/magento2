<?php
/**
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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Helper;

class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Helper\String
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Helper\String');
    }

    /**
     * @covers \Magento\Core\Helper\String::truncate
     * @covers \Magento\Core\Helper\String::strlen
     * @covers \Magento\Core\Helper\String::substr
     */
    public function testTruncate()
    {
        $string = '1234567890';
        $this->assertEquals('12...', $this->_helper->truncate($string, 5));

        $words = '123 456 789';
        $remainder = '';
        $this->assertEquals('123...', $this->_helper->truncate($words, 8, '...', $remainder, false));
    }

    /**
     * @covers \Magento\Core\Helper\String::splitInjection
     * @covers \Magento\Core\Helper\String::strrev
     */
    public function testSplitInjection()
    {
        $string = '1234567890';
        $this->assertEquals('1234 5678 90', $this->_helper->splitInjection($string, 4));
    }

    public function testStrSplit()
    {
        $this->assertEquals(array(), $this->_helper->strSplit(''));
        $this->assertEquals(array('1', '2', '3', '4'), $this->_helper->strSplit('1234', 1));
        $this->assertEquals(array('1', '2', ' ', '3', '4'), $this->_helper->strSplit('12 34', 1, false, true));
        $this->assertEquals(array(
            '12345', '123', '12345', '6789'
        ), $this->_helper->strSplit('12345  123    123456789', 5, true, true));
    }

    /**
     * Bug: $maxWordLength parameter has a misleading name. It limits qty of words in the result.
     */
    public function testSplitWords()
    {
        $words = '123  123  45 789';
        $this->assertEquals(array('123', '123', '45'), $this->_helper->splitWords($words, false, 3));
        $this->assertEquals(array('123', '45'), $this->_helper->splitWords($words, true, 2));
    }

    public function testCleanString()
    {
        $string = '12345';
        $this->assertEquals($string, $this->_helper->cleanString($string));
    }

    public function testStrpos()
    {
        $this->assertEquals(1, $this->_helper->strpos('123', 2));
    }
}
