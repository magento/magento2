<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\User\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests Magento\User\Model\Authorization\AdminSessionUserContext
 */
class AdminSessionUserContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
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
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

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
