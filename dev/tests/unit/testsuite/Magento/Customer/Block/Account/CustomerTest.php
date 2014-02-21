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

namespace Magento\Customer\Block\Account;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCustomerName()
    {
        $customer = $this->getMock('Magento\Customer\Model\Customer', array(), array(), '', false);
        $customer->expects($this->once())->method('getName')->will($this->returnValue('John Doe'));

        $escapedName = new \stdClass();
        $escaper = $this->getMock('Magento\Escaper', array(), array(), '', false);
        $escaper
            ->expects($this->once())->method('escapeHtml')->with('John Doe')->will($this->returnValue($escapedName));

        $context = $this->getMock('Magento\View\Element\Template\Context', array(), array(), '', false);
        $context->expects($this->once())->method('getEscaper')->will($this->returnValue($escaper));

        $session = $this->getMock('Magento\Customer\Model\Session', array(), array(), '', false);
        $session->expects($this->once())->method('getCustomer')->will($this->returnValue($customer));

        $block = new \Magento\Customer\Block\Account\Customer($context, $session);

        $this->assertSame($escapedName, $block->getCustomerName());
    }
}
