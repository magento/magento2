<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller;

use Magento\TestFramework\TestCase\AbstractController;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

class SendTest extends AbstractController
{
    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var FormKey */
    private $formKey;

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setUp()
    {
        parent::setUp();
        $logger = $this->createMock(LoggerInterface::class);
        $session = Bootstrap::getObjectManager()->create(
            Session::class,
            [$logger]
        );
        $this->accountManagement = Bootstrap::getObjectManager()->create(AccountManagementInterface::class);
        $this->formKey = Bootstrap::getObjectManager()->create(FormKey::class);
        $customer = $this->accountManagement->authenticate('customer@example.com', 'password');
        $session->setCustomerDataAsLoggedIn($customer);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testExecutePost()
    {
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue(
                [
                    'form_key' => $this->formKey->getFormKey(),
                    'emails' => 'example1@gmail.com, example2@gmail.com, example3@gmail.com'
                ]
            );

        $this->dispatch('wishlist/index/send');
        $this->assertRedirect($this->stringContains('wishlist/index/index'));
        $this->assertSessionMessages(
            $this->equalTo(['Your wish list has been shared.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
