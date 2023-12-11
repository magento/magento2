<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\CustomerData;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Persistent\CustomerData\Persistent;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\Session as PersistentSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PersistentTest extends TestCase
{
    /**
     * Stub customer id
     */
    private const STUB_CUSTOMER_ID = 1;

    /**
     * Stub customer name
     */
    private const STUB_CUSTOMER_NAME = 'Adam John';

    /**
     * @var Persistent
     */
    private $customerData;

    /**
     * @var Session|MockObject
     */
    private $persistentSessionHelperMock;

    /**
     * @var View|MockObject
     */
    private $customerViewHelperMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->persistentSessionHelperMock = $this->createMock(Session::class);
        $this->customerViewHelperMock = $this->createMock(View::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);

        $objectManager = new ObjectManagerHelper($this);

        $this->customerData = $objectManager->getObject(
            Persistent::class,
            [
                'persistentSession' => $this->persistentSessionHelperMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'customerRepository' => $this->customerRepositoryMock
            ]
        );
    }

    /**
     * Test getSectionData() when disable persistent
     */
    public function testGetSectionDataWhenDisablePersistent()
    {
        $this->persistentSessionHelperMock->method('isPersistent')->willReturn(false);

        $this->assertEquals([], $this->customerData->getSectionData());
    }

    /**
     * Test getSectionData() when customer doesn't login
     */
    public function testGetSectionDataWhenCustomerNotLoggedInReturnsEmptyArray()
    {
        $this->persistentSessionHelperMock->method('isPersistent')->willReturn(true);

        $persistentSessionMock = $this->getMockBuilder(PersistentSession::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $persistentSessionMock->method('getCustomerId')->willReturn(null);
        $this->persistentSessionHelperMock->method('getSession')->willReturn($persistentSessionMock);

        $this->assertEquals([], $this->customerData->getSectionData());
    }

    /**
     * Test getSectionData() when customer login and enable persistent
     */
    public function testGetSectionDataCustomerLoginAndEnablePersistent()
    {
        $this->persistentSessionHelperMock->method('isPersistent')->willReturn(true);

        $persistentSessionMock = $this->getMockBuilder(PersistentSession::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $persistentSessionMock->method('getCustomerId')->willReturn(self::STUB_CUSTOMER_ID);
        $this->persistentSessionHelperMock->method('getSession')->willReturn($persistentSessionMock);

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerRepositoryMock->method('getById')->with(self::STUB_CUSTOMER_ID)->willReturn($customerMock);
        $this->customerViewHelperMock->method('getCustomerName')->with($customerMock)
            ->willReturn(self::STUB_CUSTOMER_NAME);

        $this->assertEquals(
            [
                'fullname' => self::STUB_CUSTOMER_NAME
            ],
            $this->customerData->getSectionData()
        );
    }
}
