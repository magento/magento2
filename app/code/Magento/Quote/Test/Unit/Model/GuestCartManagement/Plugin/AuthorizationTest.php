<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\GuestCartManagement\Plugin;

use Magento\Quote\Model\GuestCartManagement\Plugin\Authorization;

class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Authorization
     */
    private $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagementMock;

    protected function setUp()
    {
        $this->userContextMock = $this->getMock('Magento\Authorization\Model\UserContextInterface');
        $this->quoteManagementMock = $this->getMock(
            'Magento\Quote\Model\GuestCart\GuestCartManagement',
            [],
            [],
            '',
            false
        );
        $this->plugin = new Authorization(
            $this->userContextMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedMessage Cannot assign customer to the given cart. You don't have permission for this operation.
     */
    public function testBeforeAssignCustomer()
    {
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn('10');
        $this->plugin->beforeAssignCustomer($this->quoteManagementMock, 1, 2, 1);
    }
}
