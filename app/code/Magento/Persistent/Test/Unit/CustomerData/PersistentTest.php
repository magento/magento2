<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\CustomerData;

use PHPUnit\Framework\TestCase;
use Magento\Persistent\CustomerData\Persistent;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\View;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\Session as PersistentSession;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PersistentTest extends TestCase
{
    /**
     * @var Persistent
     */
    private $customerData;

    /**
     * @var Session
     */
    private $persistentSessionHelperMock;

    /**
     * @var View
     */
    private $customerViewHelperMock;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryMock;

    /**
     * Setup environment for test
     */
    protected function setUp()
    {
        $this->persistentSessionHelperMock = $this->createMock(Session::class);
        $this->customerViewHelperMock = $this->createMock(View::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);

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
    public function testGetSectionDataWithNotLogin()
    {
        $this->persistentSessionHelperMock->method('isPersistent')->willReturn(true);

        $persistentSessionMock = $this->createPartialMock(PersistentSession::class, ['getCustomerId']);
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

        $persistentSessionMock = $this->createPartialMock(PersistentSession::class, ['getCustomerId']);
        $persistentSessionMock->method('getCustomerId')->willReturn(1);
        $this->persistentSessionHelperMock->method('getSession')->willReturn($persistentSessionMock);

        $customerMock = $this->createMock(CustomerInterface::class);
        $this->customerRepositoryMock->method('getById')->with(1)->willReturn($customerMock);
        $this->customerViewHelperMock->method('getCustomerName')->with($customerMock)->willReturn('Adam John');

        $this->assertEquals(
            [
                'fullname' => 'Adam John'
            ],
            $this->customerData->getSectionData()
        );
    }
}
