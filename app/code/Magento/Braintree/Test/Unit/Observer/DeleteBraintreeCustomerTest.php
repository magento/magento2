<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class DeleteBraintreeCustomerTest
 */
class DeleteBraintreeCustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Observer\DeleteBraintreeCustomer
     */
    protected $deleteBraintreeCustomerObserver;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Model\Config\Cc|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Braintree\Model\Vault|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vaultMock;

    /**
     * @var \Magento\Braintree\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();
        $this->vaultMock = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->deleteBraintreeCustomerObserver = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Observer\DeleteBraintreeCustomer',
            [
                'vault' => $this->vaultMock,
                'config' => $this->configMock,
                'helper' => $this->helperMock
            ]
        );
    }

    /**
     * @param bool $isActive
     * @param bool $isExistingCustomer
     * @param bool $deleteCustomer
     * @dataProvider deleteBraintreeCustomerDataProvider
     */
    public function testDeleteBraintreeCustomer($isActive, $isExistingCustomer, $deleteCustomer)
    {
        $braintreeCustoemrId = 'braintreeCustomerId';
        $customerId = '10002';
        $customerEmail = 'John@example.com';

        $this->configMock->expects($this->once())
            ->method('isActive')
            ->willReturn($isActive);

        $customer = new \Magento\Framework\DataObject(
            [
                'id' => $customerId,
                'email' => $customerEmail,
            ]
        );

        $this->helperMock->expects($this->any())
            ->method('generateCustomerId')
            ->with($customerId, $customerEmail)
            ->willReturn($braintreeCustoemrId);

        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => new \Magento\Framework\DataObject(
                    [
                        'customer' => $customer,
                    ]
                ),
            ]
        );

        $this->vaultMock->expects($this->any())
            ->method('exists')
            ->with($braintreeCustoemrId)
            ->willReturn($isExistingCustomer);

        if ($deleteCustomer) {
            $this->vaultMock->expects($this->once())
                ->method('deleteCustomer')
                ->with($braintreeCustoemrId);
        } else {
            $this->vaultMock->expects($this->never())
                ->method('deleteCustomer');
        }

        $this->assertEquals(
            $this->deleteBraintreeCustomerObserver,
            $this->deleteBraintreeCustomerObserver->execute($observer)
        );
    }

    public function deleteBraintreeCustomerDataProvider()
    {
        return [
            'not_active' => [
                'is_active' => false,
                'is_existing_customer' => true,
                'delete_customer' => false,
            ],
            'active_not_existing_customer' => [
                'is_active' => true,
                'is_existing_customer' => false,
                'delete_customer' => false,
            ],
            'active_existing_customer' => [
                'is_active' => true,
                'is_existing_customer' => true,
                'delete_customer' => true,
            ],
        ];
    }
}
