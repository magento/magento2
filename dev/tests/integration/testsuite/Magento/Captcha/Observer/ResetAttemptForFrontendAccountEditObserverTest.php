<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Observer;

use Magento\Captcha\Model\ResourceModel\Log as CaptchaLog;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ResetAttemptForFrontendAccountEditObserverTest
 *
 * Test for checking that the customer login attempts are removed after account details edit
 */
class ResetAttemptForFrontendAccountEditObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Captcha/_files/failed_logins_frontend.php
     */
    public function testAccountEditRemovesFailedAttempts()
    {
        $customerEmail = 'mageuser@dummy.com';
        $captchaLogFactory = $this->objectManager->get(LogFactory::class);
        $eventManager = $this->objectManager->get(ManagerInterface::class);

        $eventManager->dispatch(
            'customer_account_edited',
            ['email' => $customerEmail]
        );

        /**
         * @var CaptchaLog $captchaLog
         */
        $captchaLog = $captchaLogFactory->create();

        self::assertEquals(0, $captchaLog->countAttemptsByUserLogin($customerEmail));
    }
}
