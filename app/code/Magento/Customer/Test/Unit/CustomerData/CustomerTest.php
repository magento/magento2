<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\CustomerData;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\CustomerData\Customer as CustomerData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CustomerData
     */
    private $customerData;

    /**
     * @var CurrentCustomer
     */
    private $currentCustomerMock;

    /**
     * @var View
     */
    private $customerViewHelperMock;

    /**
     * Setup environment to test
     */
    protected function setUp(): void
    {
        $this->currentCustomerMock = $this->createMock(CurrentCustomer::class);
        $this->customerViewHelperMock = $this->createMock(View::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->customerData = $this->objectManagerHelper->getObject(
            CustomerData::class,
            [
                'currentCustomer' => $this->currentCustomerMock,
                'customerViewHelper' => $this->customerViewHelperMock
            ]
        );
    }

    /**
     * Test getSectionData() without customer Id
     */
    public function testGetSectionDataWithoutCustomerId()
    {
        $this->currentCustomerMock->expects($this->any())->method('getCustomerId')->willReturn(null);
        $this->assertEquals([], $this->customerData->getSectionData());
    }

    /**
     * Test getSectionData() with customer
     */
    public function testGetSectionDataWithCustomer()
    {
        $this->currentCustomerMock->expects($this->any())->method('getCustomerId')->willReturn(1);
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock->expects($this->any())->method('getFirstname')->willReturn('John');
        $customerMock->expects($this->any())->method('getWebsiteId')->willReturn(1);
        $this->currentCustomerMock->expects($this->any())->method('getCustomer')->willReturn($customerMock);
        $this->customerViewHelperMock->expects($this->any())->method('getCustomerName')
            ->with($customerMock)
            ->willReturn('John Adam');

        $this->assertEquals(
            [
                'fullname' => 'John Adam',
                'firstname' => 'John',
                'websiteId' => 1,
            ],
            $this->customerData->getSectionData()
        );
    }
}
