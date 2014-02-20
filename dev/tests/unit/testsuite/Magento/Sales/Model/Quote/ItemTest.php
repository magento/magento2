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
 * @package     Magento_Sales
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Model\Quote;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Quote\Item
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            array('__wakeup'),
            array(),
            '',
            false
        );
    }

    public function testGetAddress()
    {
        $quote = $this->getMock(
            'Magento\Sales\Model\Quote',
            array('getShippingAddress', 'getBillingAddress', '__wakeup'),
            array(),
            '',
            false
        );
        $quote->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue('shipping'));
        $quote->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue('billing'));

        $this->_model->setQuote($quote);

        $quote->setItemsQty(2);
        $quote->setVirtualItemsQty(1);
        $this->assertEquals('shipping', $this->_model->getAddress(), 'Wrong shipping address');

        $quote->setItemsQty(2);
        $quote->setVirtualItemsQty(2);
        $this->assertEquals('billing', $this->_model->getAddress(), 'Wrong billing address');
    }
}
