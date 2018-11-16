<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Controller;

use Magento\TestFramework\TestCase\AbstractController;
use Magento\Framework\App\Config\Value;

/**
 * Test Unsubscriber controller.
 *
 * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
 * @magentoAppArea     frontend
 */
class UnSubscriberTest extends AbstractController
{
    /**
     * @var Subscriber
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Newsletter\Model\Subscriber::class
        );
        parent::setUp();
    }

    /**
     * @return void
     */
    public function testSuccessUnsubscribeSubscribedUser()
    {
        $subscriber = $this->model->loadByCustomerId(1);
        $this->getRequest()
            ->setParam('id', $subscriber->getId())
            ->setParam('code', 'zxayquyajua23iq29gxwu2eax2qb6gvy');

        $this->dispatch('newsletter/subscriber/unsubscribe');

        $this->assertSessionMessages($this->equalTo(['You unsubscribed.']));
        $this->assertRedirect($this->equalTo($this->getBaseUrl() . 'index.php/'));
    }

    /**
     * @return void
     */
    public function testFailureUnsubscribeSubscribedUser()
    {
        $subscriber = $this->model->loadByCustomerId(1);
        $this->getRequest()
            ->setParam('id', $subscriber->getId())
            ->setParam('code', 'randomcode');

        $this->dispatch('newsletter/subscriber/unsubscribe');

        $this->assertSessionMessages($this->equalTo(['This is an invalid subscription confirmation code.']));
        $this->assertRedirect($this->equalTo($this->getBaseUrl() . 'index.php/'));
    }

    /**
     * @return string
     */
    private function getBaseUrl()
    {
        $configValue = $this->_objectManager->create(Value::class);
        $configValue->load('web/unsecure/base_url', 'path');

        return $configValue->getValue() ?: 'http://localhost/';
    }
}
