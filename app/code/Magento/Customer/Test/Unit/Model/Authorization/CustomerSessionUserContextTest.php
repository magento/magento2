<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Authorization\CustomerSessionUserContext;
use Magento\Customer\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Customer\Model\Authorization\CustomerSessionUserContext
 */
class CustomerSessionUserContextTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CustomerSessionUserContext
     */
    protected $customerSessionUserContext;

    /**
     * @var Session
     */
    protected $customerSession;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $this->customerSessionUserContext = $this->objectManager->getObject(
            CustomerSessionUserContext::class,
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
