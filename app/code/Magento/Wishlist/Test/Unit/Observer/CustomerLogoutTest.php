<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Observer;

use Magento\Customer\Model\Session;
use Magento\Wishlist\Observer\CustomerLogout as Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Event\Observer as EventObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerLogoutTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var Session|MockObject
     */
    protected $customerSession;

    protected function setUp(): void
    {
        $this->customerSession = $this->createPartialMockWithReflection(
            Session::class,
            ['setWishlistItemCount', 'isLoggedIn', 'getCustomerId']
        );

        $this->observer = new Observer(
            $this->customerSession
        );
    }

    public function testExecute()
    {
        $event = $this->createMock(EventObserver::class);
        /** @var EventObserver $event */

        $this->customerSession->expects($this->once())
            ->method('setWishlistItemCount')
            ->with(0);

        $this->observer->execute($event);
    }
}
