<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Plugin;

use Magento\Customer\CustomerData\Customer;
use Magento\Customer\Model\Session;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Model\Plugin\CustomerData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerDataTest extends TestCase
{
    /**
     * @var CustomerData
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->subjectMock = $this->createMock(Customer::class);
        $this->plugin = new CustomerData(
            $this->helperMock,
            $this->customerSessionMock,
            $this->persistentSessionMock
        );
    }

    public function testAroundGetSectionDataForPersistentSession()
    {
        $result = 'result';
        $proceed = function () use ($result) {
            return $result;
        };

        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);

        $this->assertEquals([], $this->plugin->aroundGetSectionData($this->subjectMock, $proceed));
    }

    public function testAroundGetSectionData()
    {
        $result = 'result';
        $proceed = function () use ($result) {
            return $result;
        };

        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(false);

        $this->assertEquals($result, $this->plugin->aroundGetSectionData($this->subjectMock, $proceed));
    }
}
