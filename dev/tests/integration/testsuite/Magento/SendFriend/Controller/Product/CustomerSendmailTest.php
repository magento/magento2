<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Controller\Product;

use Magento\Captcha\Model\DefaultModel;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Framework\Data\Form\FormKey;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\Captcha\Helper\Data as CaptchaHelper;

class CustomerSendmailTest extends AbstractController
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @throws LocalizedException
     */
    protected function setUp()
    {
        parent::setUp();
        $this->accountManagement = $this->_objectManager->create(AccountManagementInterface::class);
        $this->formKey = $this->_objectManager->create(FormKey::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->session = $this->_objectManager->create(
            Session::class,
            [$logger]
        );
        $this->captchaHelper = $this->_objectManager->create(CaptchaHelper::class);
        $customer = $this->accountManagement->authenticate('customer@example.com', 'password');
        $this->session->setCustomerDataAsLoggedIn($customer);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testExecute()
    {
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue(
                [
                    'form_key' => $this->formKey->getFormKey(),
                    'sender' => [
                        'name' => 'customer',
                        'email' => 'customer@example.com',
                        'message' => 'example message'
                    ],
                    'id' => 1,
                    'recipients' => [
                        'name' => ['John'],
                        'email' => ['example1@gmail.com']
                    ]

                ]
            );

        $this->dispatch('sendfriend/product/sendmail');
        $this->assertSessionMessages(
            $this->equalTo(['The link to a friend was sent.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default_store customer/captcha/forms product_sendtofriend_form
     */
    public function testWithCaptchaFailed()
    {
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue(
                [
                    'form_key' => $this->formKey->getFormKey(),
                    'sender' => [
                        'name' => 'customer',
                        'email' => 'customer@example.com',
                        'message' => 'example message'
                    ],
                    'id' => 1,
                    'captcha' => [
                        'product_sendtofriend_form' => 'test'
                    ],
                    'recipients' => [
                        'name' => ['John'],
                        'email' => ['example1@gmail.com']
                    ]

                ]
            );

        $this->dispatch('sendfriend/product/sendmail');
        $this->assertSessionMessages(
            $this->equalTo(['Incorrect CAPTCHA']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default_store customer/captcha/forms product_sendtofriend_form
     *
     */
    public function testWithCaptchaSuccess()
    {
        /** @var DefaultModel $captchaModel */
        $captchaModel = $this->captchaHelper->getCaptcha('product_sendtofriend_form');
        $captchaModel->generate();
        $word = $captchaModel->getWord();
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue(
                [
                    'form_key' => $this->formKey->getFormKey(),
                    'sender' => [
                        'name' => 'customer',
                        'email' => 'customer@example.com',
                        'message' => 'example message'
                    ],
                    'id' => 1,
                    'captcha' => [
                        'product_sendtofriend_form' => $word
                    ],
                    'recipients' => [
                        'name' => ['John'],
                        'email' => ['example1@gmail.com']
                    ]
                ]
            );

        $this->dispatch('sendfriend/product/sendmail');
        $this->assertSessionMessages(
            $this->equalTo(['The link to a friend was sent.']),
            MessageInterface::TYPE_SUCCESS
        );
    }
}
