<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Model\Plugin;

class CustomerDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Model\Plugin\CustomerData
     */
    protected $plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->subjectMock = $this->createMock(\Magento\Customer\CustomerData\Customer::class);
        $this->plugin = new \Magento\Persistent\Model\Plugin\CustomerData(
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
