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

namespace Magento\Checkout\Model\Type;

/**
 * Test class for \Magento\Checkout\Model\Type\AbstractType
 */
class AbstractTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Model\Type\AbstractType */
    protected $model;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSession;

    /** @var \Magento\Sales\Model\OrderFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderFactory;

    /** @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerAddressService;

    /** @var \Magento\Customer\Service\V1\Data\Customer|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerObject;

    public function setUp()
    {
        $this->checkoutSession = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', [], [], '', false);
        $this->customerAddressService = $this->getMock('Magento\Customer\Service\V1\CustomerAddressServiceInterface');

        $this->customerObject = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $this->model = $this->getMockForAbstractClass(
            '\Magento\Checkout\Model\Type\AbstractType',
            [
                'checkoutSession' => $this->checkoutSession,
                'customerSession' => $this->customerSession,
                'orderFactory' => $this->orderFactory,
                'customerAddressService' => $this->customerAddressService
            ],
            '',
            true
        );
    }

    public function testGetCheckoutSession()
    {
        $this->assertSame($this->checkoutSession, $this->model->getCheckoutSession());
    }

    public function testGetCustomerSession()
    {
        $this->assertSame($this->customerSession, $this->model->getCustomerSession());
    }

    public function testGetCustomer()
    {
        $this->customerSession->expects($this->once())->method('getCustomerDataObject')
            ->will($this->returnValue($this->customerObject));
        $this->assertSame($this->customerObject, $this->model->getCustomer());
    }

    public function testGetQuote()
    {
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $this->checkoutSession->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));

        $this->assertSame($quoteMock, $this->model->getQuote());
    }

    public function testGetQuoteItems()
    {
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $itemMock = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);
        $this->checkoutSession->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('getAllItems')->will($this->returnValue([$itemMock]));

        $this->assertEquals([$itemMock], $this->model->getQuoteItems());
    }

    /**
     * @param string $serviceMethod
     * @param string $modelMethod
     * @dataProvider getDefaultAddressDataProvider
     */
    public function testGetCustomerDefaultShippingAddress($serviceMethod, $modelMethod)
    {
        $address = $this->getMock('Magento\Customer\Service\V1\Data\Address', [], [], '', false);
        $customerId = 1;
        $this->customerSession->expects($this->once())->method('getCustomerDataObject')
            ->will($this->returnValue($this->customerObject));
        $this->customerObject->expects($this->once())->method('getId')
            ->will($this->returnValue($customerId));
        $this->customerAddressService->expects($this->once())->method($serviceMethod)->with($customerId)
            ->will($this->returnValue($address));
        $this->customerAddressService->expects($this->never())->method('getAddresses');

        $this->assertSame($address, $this->model->$modelMethod());
    }

    /**
     * @param string $serviceMethod
     * @param string $modelMethod
     * @dataProvider getDefaultAddressDataProvider
     */
    public function testGetCustomerDefaultShippingAddressIfDefaultNotAvailable($serviceMethod, $modelMethod)
    {
        $address = $this->getMock('Magento\Customer\Service\V1\Data\Address', [], [], '', false);
        $customerId = 1;
        $this->customerSession->expects($this->once())->method('getCustomerDataObject')
            ->will($this->returnValue($this->customerObject));
        $this->customerObject->expects($this->once())->method('getId')
            ->will($this->returnValue($customerId));
        $this->customerAddressService->expects($this->once())->method($serviceMethod)->with($customerId)
            ->will($this->returnValue(null));
        $this->customerAddressService->expects($this->once())->method('getAddresses')->with($customerId)
            ->will($this->returnValue([$address]));

        $this->assertSame($address, $this->model->$modelMethod());
    }

    public function getDefaultAddressDataProvider()
    {
        return [
            ['getDefaultShippingAddress', 'getCustomerDefaultShippingAddress'],
            ['getDefaultBillingAddress', 'getCustomerDefaultBillingAddress'],
        ];
    }
}
