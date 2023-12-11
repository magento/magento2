<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * @magentoAppIsolation enabled
 */
class ShareTest extends AbstractController
{
    /**
     * Test share wishlist with correct data
     *
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testSuccessfullyShareWishlist()
    {
        $this->login(1);
        $this->prepareRequestData();
        $this->dispatch('wishlist/index/send/');

        $this->assertSessionMessages(
            $this->equalTo(['Your wish list has been shared.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Test share wishlist with incorrect data
     *
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testShareWishlistWithoutEmails()
    {
        $this->login(1);
        $this->prepareRequestData(true);
        $this->dispatch('wishlist/index/send/');

        $this->assertSessionMessages(
            $this->equalTo(['Please enter an email address.']),
            MessageInterface::TYPE_ERROR
        );
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
        $session = $this->_objectManager->get(Session::class);
        $session->loginById($customerId);
    }

    /**
     * Prepares the request with data
     *
     * @param bool $invalidData
     * @return void
     */
    private function prepareRequestData($invalidData = false)
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $emails = !$invalidData ? 'email-1@example.com,email-2@example.com' : '';

        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'emails' => $emails,
            'message' => '',
            'form_key' => $formKey->getFormKey(),
        ];

        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setPostValue($post);
    }
}
