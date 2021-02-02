<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests Magento\Customer\Model\Authorization\CustomerSessionUserContext
 */
class CustomerSessionUserContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Model\Authorization\CustomerSessionUserContext
     */
    protected $customerSessionUserContext;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->customerSessionUserContext = $this->objectManager->getObject(
            \Magento\Customer\Model\Authorization\CustomerSessionUserContext::class,
            ['customerSession' => $this->customerSession]
        );
    }

    public function testGetUserIdExist()
    {
        $userId = 1;
        $this->setupUserId($userId);
        $this->assertEquals($userId, $this->customerSessionUserContext->getUserId());
    }

    public function testGetUserIdDoesNotExist()
    {
        $userId = null;
        $this->setupUserId($userId);
        $this->assertEquals($userId, $this->customerSessionUserContext->getUserId());
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_CUSTOMER, $this->customerSessionUserContext->getUserType());
    }

    /**
     * @param int|null $userId
     * @return void
     */
    public function setupUserId($userId)
    {
        $this->customerSession->expects($this->once())
            ->method('getId')
            ->willReturn($userId);
    }
}
