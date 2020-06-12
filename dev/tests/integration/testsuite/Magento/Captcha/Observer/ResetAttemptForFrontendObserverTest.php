<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Observer;

use Magento\Captcha\Model\ResourceModel\Log as CaptchaLog;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ResetAttemptForFrontendObserverTest
 *
 * Test for checking that the customer login attempts are removed after a successful login
 */
class ResetAttemptForFrontendObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Captcha/_files/failed_logins_frontend.php
     */
    public function testSuccesfulLoginRemovesFailedAttempts()
    {
        $customerEmail = 'mageuser@dummy.com';
        $customerFactory = $this->objectManager->get(CustomerFactory::class);
        $captchaLogFactory = $this->objectManager->get(LogFactory::class);
        $eventManager = $this->objectManager->get(ManagerInterface::class);

        /** @var Customer $customer */
        $customer = $customerFactory->create();
        $customer->setEmail($customerEmail);

        $eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customer, 'password' => 'some_password']
        );

        /**
         * @var CaptchaLog $captchaLog
         */
        $captchaLog = $captchaLogFactory->create();

        self::assertEquals(0, $captchaLog->countAttemptsByUserLogin($customerEmail));
    }
}
