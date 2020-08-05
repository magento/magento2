<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Ajax;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test Subscriber status ajax
 */
class StatusTest extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->json = $this->_objectManager->get(SerializerInterface::class);
    }

    /**
     * Check newsletter subscription status verification
     *
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @dataProvider ajaxSubscriberDataProvider
     * @param bool $expStatus
     * @param string $email
     *
     * @return void
     */
    public function testExecute(bool $expStatus, string $email): void
    {
        $this->getRequest()->setParam('email', $email);
        $this->dispatch('newsletter/ajax/status');
        $actual = $this->json->unserialize($this->getResponse()->getBody());

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
            'empty_email_parameter' => [false, ''],
            'invalid_email' => [false, 'invalid_email.com'],
            'unsubscribed_email' => [false, 'sample@email.com'],
            'subscribed_guest_email' => [true, 'customer_two@example.com'],
            'subscribed_registered_customer_email' => [false, 'customer@example.com'],
        ];
    }
}
