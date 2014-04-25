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

/**
 * @magentoDbIsolation enabled
 */
class ManageTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $coreSession;

    protected function setUp()
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $this->customerSession->setCustomerId(1);
        $this->coreSession = $objectManager->get('Magento\Framework\Session\Generic');
        $this->coreSession->setData('_form_key', 'formKey');
    }

    protected function tearDown()
    {
        $this->customerSession->setCustomerId(null);
        $this->coreSession->unsData('_form_key');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSaveAction()
    {
        $this->getRequest()
            ->setParam('form_key', 'formKey')
            ->setParam('is_subscribed', '1');
        $this->dispatch('newsletter/manage/save');

        $this->assertRedirect($this->stringContains('customer/account/'));

        /**
         * Check that errors
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that success message
         */
        $this->assertSessionMessages(
            $this->equalTo(['We saved the subscription.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSaveActionRemoveSubscription()
    {
        $this->getRequest()
            ->setParam('form_key', 'formKey')
            ->setParam('is_subscribed', '0');
        $this->dispatch('newsletter/manage/save');

        $this->assertRedirect($this->stringContains('customer/account/'));

        /**
         * Check that errors
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that success message
         */
        $this->assertSessionMessages(
            $this->equalTo(['We removed the subscription.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
