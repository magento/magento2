<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests Magento\User\Model\Authorization\AdminSessionUserContext
 */
class AdminSessionUserContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\User\Model\Authorization\AdminSessionUserContext
     */
    protected $adminSessionUserContext;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $adminSession;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->adminSession = $this->getMockBuilder('Magento\Backend\Model\Auth\Session')
            ->disableOriginalConstructor()
            ->setMethods(['hasUser', 'getUser', 'getId'])
            ->getMock();

        $this->adminSessionUserContext = $this->objectManager->getObject(
            'Magento\User\Model\Authorization\AdminSessionUserContext',
            ['adminSession' => $this->adminSession]
        );
    }

    public function testGetUserIdExist()
    {
        $userId = 1;

        $this->setupUserId($userId);

        $this->assertEquals($userId, $this->adminSessionUserContext->getUserId());
    }

    public function testGetUserIdDoesNotExist()
    {
        $userId = null;

        $this->setupUserId($userId);

        $this->assertEquals($userId, $this->adminSessionUserContext->getUserId());
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_ADMIN, $this->adminSessionUserContext->getUserType());
    }

    /**
     * @param int|null $userId
     * @return void
     */
    public function setupUserId($userId)
    {
        $this->adminSession->expects($this->once())
            ->method('hasUser')
            ->will($this->returnValue($userId));

        if ($userId) {
            $this->adminSession->expects($this->once())
                ->method('getUser')
                ->will($this->returnSelf());

            $this->adminSession->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($userId));
        }
    }
}
