<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Model\Authorization\GuestUserContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Webapi\Model\Authorization\GuestUserContext
 */
class GuestUserContextTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var GuestUserContext
     */
    protected $guestUserContext;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->guestUserContext = $this->objectManager->getObject(
            GuestUserContext::class
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
