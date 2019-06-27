<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Controller;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Class SendmailTest
 */
class SendmailTest extends AbstractController
{
    /**
     * Share the product to friend as logged in customer
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SendFriend/_files/disable_allow_guest_config.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testSendActionAsLoggedIn()
    {
        $product = $this->getProduct();
        $this->login(1);
        $this->prepareRequestData();

        $this->dispatch('sendfriend/product/sendmail/id/' . $product->getId());
        $this->assertSessionMessages(
            $this->equalTo(['The link to a friend was sent.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Share the product to friend as guest customer
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 1
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testSendActionAsGuest()
    {
        $product = $this->getProduct();
        $this->prepareRequestData();

        $this->dispatch('sendfriend/product/sendmail/id/' . $product->getId());
        $this->assertSessionMessages(
            $this->equalTo(['The link to a friend was sent.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Share the product to friend as guest customer with invalid post data
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 1
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testSendActionAsGuestWithInvalidData()
    {
        $product = $this->getProduct();
        $this->prepareRequestData(true);

        $this->dispatch('sendfriend/product/sendmail/id/' . $product->getId());
        $this->assertSessionMessages(
            $this->equalTo(['Invalid Sender Email']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Share the product invisible in catalog to friend as guest customer
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 1
     * @magentoDataFixture Magento/Catalog/_files/simple_products_not_visible_individually.php
     */
    public function testSendInvisibleProduct()
    {
        $product = $this->getInvisibleProduct();
        $this->prepareRequestData();

        $this->dispatch('sendfriend/product/sendmail/id/' . $product->getId());
        $this->assert404NotFound();
    }

    /**
     * @return ProductInterface
     */
    private function getProduct()
    {
        return $this->_objectManager->get(ProductRepositoryInterface::class)->get('custom-design-simple-product');
    }

    /**
     * @return ProductInterface
     */
    private function getInvisibleProduct()
    {
        return $this->_objectManager->get(ProductRepositoryInterface::class)->get('simple_not_visible_1');
    }

    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId)
    {
        /** @var Session $session */
        $session = Bootstrap::getObjectManager()
            ->get(Session::class);
        $session->loginById($customerId);
    }

    /**
     * @param bool $invalidData
     * @return void
     */
    private function prepareRequestData($invalidData = false)
    {
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'sender' => [
                'name' => 'Test',
                'email' => 'test@example.com',
                'message' => 'Message',
            ],
            'recipients' => [
                'name' => [
                    'Recipient 1',
                    'Recipient 2'
                ],
                'email' => [
                    'r1@example.com',
                    'r2@example.com'
                ]
            ],
            'form_key' => $formKey->getFormKey(),
        ];
        if ($invalidData) {
            unset($post['sender']['email']);
        }

        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setPostValue($post);
    }
}
