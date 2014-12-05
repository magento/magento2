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
     * @var \Magento\Paypal\Model\Express\Checkout | \Magento\Paypal\Model\Express\Checkout
     */
    protected $checkoutModel;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \'Magento\Sales\Model\Quote
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \\Magento\Sales\Model\Service\Quote
     */
    protected $serviceQuote;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \\Magento\Sales\Model\Service\QuoteFactory
     */
    protected $quoteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \\Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $customerAccountServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \\Magento\Customer\Service\V1\Data\AddressBuilderFactory
     */
    protected $addressBuilderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \\Magento\Framework\Object\Copy
     */
    protected $objectCopyServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \\Magento\Customer\Model\Session
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Customer
     */
    protected $customerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->customerMock = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
        $this->quoteMock = $this->getMock('Magento\Sales\Model\Quote',
            [
                'getId', 'assignCustomer', 'assignCustomerWithAddressChange', 'getBillingAddress',
                'getShippingAddress', 'isVirtual', 'addCustomerAddress', 'collectTotals', '__wakeup',
                'save', 'getCustomerData'
            ], [], '', false);
        $this->serviceQuote = $this->getMock('\Magento\Sales\Model\Service\Quote', [], [], '', false);
        $this->quoteFactoryMock = $this->getMock(
            '\Magento\Sales\Model\Service\QuoteFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->customerAccountServiceMock = $this->getMock(
            '\Magento\Customer\Model\AccountManagement',
            [],
            [],
            '',
            false
        );
        $this->addressBuilderFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Api\Data\AddressDataBuilderFactory'
        )
            ->setMethods(['create', 'populate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerBuilderMock =  $this->getMock(
            'Magento\Customer\Api\Data\CustomerDataBuilder',
            [
                'populateWithArray', 'setEmail', 'create', 'setPrefix', 'setFirstname', 'setMiddlename',
                'setLastname', 'setSuffix'
            ], [], '', false
        );
        $this->objectCopyServiceMock = $this->getMockBuilder('\Magento\Framework\Object\Copy')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $paypalConfigMock = $this->getMock('Magento\Paypal\Model\Config', [], [], '', false);
        $this->checkoutModel = $this->objectManager->getObject(
            'Magento\Paypal\Model\Express\Checkout',
            [
                'params'                 => [
                    'quote' => $this->quoteMock,
                    'config' => $paypalConfigMock,
                    'session' => $this->customerSessionMock
                ],
                'accountManagement' => $this->customerAccountServiceMock,
                'serviceQuoteFactory' => $this->quoteFactoryMock,
                'addressBuilderFactory' => $this->addressBuilderFactoryMock,
                'objectCopyService' => $this->objectCopyServiceMock,
                'customerBuilder' => $this->customerBuilderMock
            ]
        );
        parent::setUp();
    }

    public function testSetCustomerData()
    {
        $customerDataMock = $this->getMock('Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        $this->quoteMock->expects($this->once())->method('assignCustomer')->with($customerDataMock);
        $customerDataMock->expects($this->once())
            ->method('getId');
        $this->checkoutModel->setCustomerData($customerDataMock);
    }

    public function testSetCustomerWithAddressChange()
    {
        /** @var \Magento\Customer\Service\V1\Data\Customer $customerDataMock */
        $customerDataMock = $this->getMock('Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        /** @var \Magento\Sales\Model\Quote\Address $customerDataMock */
        $quoteAddressMock = $this->getMock('Magento\Sales\Model\Quote\Address', [], [], '', false);
        $this->quoteMock
            ->expects($this->once())
            ->method('assignCustomerWithAddressChange')
            ->with($customerDataMock, $quoteAddressMock, $quoteAddressMock);
        $customerDataMock->expects($this->once())->method('getId');
        $this->checkoutModel->setCustomerWithAddressChange($customerDataMock, $quoteAddressMock, $quoteAddressMock);
    }
}
