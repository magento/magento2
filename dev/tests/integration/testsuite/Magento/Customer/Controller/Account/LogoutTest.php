<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Model\Session;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\Attributes\CoversClass;

#[
    CoversClass(Logout::class),
]
class LogoutTest extends AbstractController
{
    /**
     * @var Session
     */
    private $customerSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerSession = $this->_objectManager->get(Session::class);
    }

    #[
        DataFixture('Magento/Customer/_files/customer.php'),
    ]
    public function testExecute(): void
    {
        $this->customerSession->setCustomerId('1');

        $sessionCleanerMock = $this->createMock(SessionCleanerInterface::class);
        $this->_objectManager->addSharedInstance($sessionCleanerMock, SessionCleanerInterface::class, true);
        $sessionCleanerMock->expects(self::never())->method('clearFor');

        $this->dispatch('customer/account/logout');
    }
}
