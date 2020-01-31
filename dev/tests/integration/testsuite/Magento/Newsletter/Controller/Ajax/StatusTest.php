<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Ajax;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test Subscriber status ajax
 */
class StatusTest extends AbstractController
{
    /**
     * Check newsletter subscription status verification
     *
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @dataProvider ajaxSubscriberDataProvider
     * @param string $expStatus
     * @param string $email
     *
     * @return void
     */
    public function testExecute(string $expStatus, string $email): void
    {
        $this->getRequest()->setParam('email', $email);
        $this->dispatch('newsletter/ajax/status');
        $actual = $this->_objectManager->get(Json::class)->unserialize($this->getResponse()->getBody());

        $this->assertEquals($expStatus, $actual['subscribed']);
    }

    /**
     * Provides data and Expected Result
     *
     * @param void
     * @return array
     */
    public function ajaxSubscriberDataProvider(): array
    {
        return [
            [false, ''],
            [false, 'sample@email.com'],
            [false, 'customer@example.com'],
            [true, 'customer_two@example.com'],
            [false, 'customer_confirm@example.com'],
            [false, 'invalid_email.com'],
        ];
    }
}
