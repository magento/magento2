<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
namespace Magento\Newsletter\Controller;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test Subscriber
 */
class SubscriberTest extends AbstractController
{
    public function testNewAction()
    {
        $this->getRequest()->setMethod('POST');

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->isEmpty());
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testNewActionUnusedEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPost([
            'email' => 'not_used@example.com'
        ]);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo(['Thank you for your subscription.']));
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testNewActionUsedEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPost([
            'email' => 'customer@example.com'
        ]);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo([
                'There was a problem with the subscription: This email address is already assigned to another user.'
            ]));
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testNewActionOwnerEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPost([
            'email' => 'customer@example.com'
        ]);
        $this->login(1);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo(['Thank you for your subscription.']));
        $this->assertRedirect($this->anything());
    }

    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\Session');
        $session->loginById($customerId);
    }
}
