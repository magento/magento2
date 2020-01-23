<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Ajax;

use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test Subscriber status ajax
 */
class StatusTest extends AbstractController
{
    const STATUS_NOT_SUBSCRIBED = '"subscribed":false';

    /**
     * Check newsletter subscription status verification
     *
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @dataProvider ajaxSubscriberDataProvider
     * @param string $email
     * @param string $expected
     *
     * @return void
     */
    public function testExecute(string $email, string $expected): void
    {
        $this->getRequest()->setParam('email', $email);
        $this->dispatch('newsletter/ajax/status');
        $actual  = $this->getResponse()->getBody();

        $this->assertContains($expected, $actual);
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
            [
                '',
                self::STATUS_NOT_SUBSCRIBED,
            ],
            [
                'sample@email.com',
                self::STATUS_NOT_SUBSCRIBED,
            ],
            [
                'customer@example.com',
                self::STATUS_NOT_SUBSCRIBED,
            ],
            [
                'customer_two@example.com',
                '"subscribed":true',
            ],
            [
                'customer_confirm@example.com',
                self::STATUS_NOT_SUBSCRIBED,
            ],
        ];
    }
}
