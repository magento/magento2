<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests Magento\Webapi\Model\Authorization\GuestUserContext
 */
class GuestUserContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Webapi\Model\Authorization\GuestUserContext
     */
    protected $guestUserContext;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->guestUserContext = $this->objectManager->getObject(
            \Magento\Webapi\Model\Authorization\GuestUserContext::class
        );
    }

    public function testGetUserId()
    {
        $this->assertSame(0, $this->guestUserContext->getUserId());
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_GUEST, $this->guestUserContext->getUserType());
    }
}
