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

namespace Magento\Payment\Model\Config\Source;

class AllmethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paymentData;

    /**
     * @var Allmethods
     */
    protected $_model;

    public function setUp()
    {
        $this->_paymentData = $this->getMockBuilder(
            'Magento\Payment\Helper\Data'
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $this->_model = new Allmethods($this->_paymentData);
    }

    public function testToOptionArray()
    {
        $expectedArray = ['key' => 'value'];
        $this->_paymentData->expects($this->once())->method('getPaymentMethodList')->with(
            true, true, true
        )->will($this->returnValue($expectedArray));
        $this->assertEquals($expectedArray, $this->_model->toOptionArray());
    }
}
