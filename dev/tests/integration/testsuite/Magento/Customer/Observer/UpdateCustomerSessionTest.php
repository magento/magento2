<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Session;
use PHPUnit\Framework\TestCase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Customer\Model\Cache\Type\Notification;

/**
 * Integration Test for class @see \Magento\Customer\Observer\UpdateCustomerSession
 *
 * @magentoAppArea frontend
 */
class UpdateCustomerSessionTest extends TestCase
{

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var NotificationStorage
     */
    private NotificationStorage $notificationStorage;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var CustomerModel
     */
    private CustomerModel $customerModel;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager  = Bootstrap::getObjectManager();
        $this->customerModel = $this->objectManager->create(CustomerModel::class);
        $this->notificationStorage = $this->objectManager->create(NotificationStorage::class);
        $this->session = $this->objectManager->create(Session::class);
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = $this->objectManager->get(StateInterface::class);
        $cacheState->setEnabled(Notification::TYPE_IDENTIFIER, true);
        parent::setUp();
    }

    /**
     * Test for verifying session is regenerated after account save
     *
     * @magentoAppArea frontend
     */
    public function testRegenerateSessionAfterSave()
    {
        $sessionId = $this->session->getSessionId();
        $email= uniqid()."@example.com";

        $this->customerModel->setData(
            [
                'email' => $email,
                'firstname'=> 'John',
                'lastname' => 'Doe'
            ]
        )->save();

        $newSessionId = $this->session->getSessionId();
        $this->assertNotEquals($sessionId, $newSessionId);
    }
}

