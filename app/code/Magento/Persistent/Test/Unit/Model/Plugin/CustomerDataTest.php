<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Model\Plugin;

class CustomerDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Plugin\CustomerData
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->helperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->persistentSessionMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->subjectMock = $this->getMock(\Magento\Customer\CustomerData\Customer::class, [], [], '', false);
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
