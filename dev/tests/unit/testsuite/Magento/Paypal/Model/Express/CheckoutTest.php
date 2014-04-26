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

namespace Magento\Paypal\Model\Express;

class CheckoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Express\Checkout
     */
    protected $_checkoutModel;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $paypalConfigMock = $this->getMock('Magento\Paypal\Model\Config', [], [], '', false);
        $this->_quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $this->_checkoutModel = $this->_objectManager->getObject(
            'Magento\Paypal\Model\Express\Checkout',
            ['params' => ['quote' => $this->_quoteMock, 'config' => $paypalConfigMock]]
        );
        parent::setUp();
    }

    public function testSetCustomerData()
    {
        /** @var \Magento\Customer\Service\V1\Data\Customer $customerDataMock */
        $customerDataMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $this->_quoteMock->expects($this->once())->method('assignCustomer')->with($customerDataMock);
        $customerDataMock->expects($this->once())->method('getId');
        $this->_checkoutModel->setCustomerData($customerDataMock);
    }

    public function testSetCustomerWithAddressChange()
    {
        /** @var \Magento\Customer\Service\V1\Data\Customer $customerDataMock */
        $customerDataMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        /** @var \Magento\Sales\Model\Quote\Address $customerDataMock */
        $quoteAddressMock = $this->getMock('Magento\Sales\Model\Quote\Address', [], [], '', false);
        $this->_quoteMock
            ->expects($this->once())
            ->method('assignCustomerWithAddressChange')
            ->with($customerDataMock, $quoteAddressMock, $quoteAddressMock);
        $customerDataMock->expects($this->once())->method('getId');
        $this->_checkoutModel->setCustomerWithAddressChange($customerDataMock, $quoteAddressMock, $quoteAddressMock);
    }
}
