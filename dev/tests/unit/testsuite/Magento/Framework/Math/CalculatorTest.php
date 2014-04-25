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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Math;

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Math\Calculator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $_scopeMock;

    public function setUp()
    {
        $this->_scopeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $this->_scopeMock->expects($this->any())
            ->method('roundPrice')
            ->will($this->returnCallback(function ($argument) {
                return round($argument, 2);
            }));

        $this->_model = new \Magento\Framework\Math\Calculator($this->_scopeMock);
    }

    /**
     * @param float $price
     * @param bool $negative
     * @param float $expected
     * @dataProvider deltaRoundDataProvider
     * @covers \Magento\Framework\Math\Calculator::deltaRound
     * @covers \Magento\Framework\Math\Calculator::__construct
     */
    public function testDeltaRound($price, $negative, $expected)
    {
        $this->assertEquals($expected, $this->_model->deltaRound($price, $negative));
    }

    /**
     * @return array
     */
    public function deltaRoundDataProvider()
    {
        return array(
            array(0, false, 0),
            array(2.223, false, 2.22),
            array(2.226, false, 2.23),
            array(2.226, true, 2.23),
        );
    }
}
