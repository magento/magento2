<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Cards;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Vault\Model\CustomerTokenManagement;
use Zend\Http\Request;

/**
 * Class DeleteActionTest
 */
class DeleteActionTest extends AbstractController
{
    /**
     * @covers \Magento\Vault\Controller\Cards\DeleteAction::execute
     * @magentoDataFixture Magento/Braintree/_files/paypal_vault_token.php
     */
    public function testExecute()
    {
        $customerId = 1;
        /** @var Session $session */
        $session = $this->_objectManager->get(Session::class);
        $session->setCustomerId($customerId);
        
        /** @var CustomerTokenManagement $tokenManagement */
        $tokenManagement = $this->_objectManager->get(CustomerTokenManagement::class);
        $tokens = $tokenManagement->getCustomerSessionTokens();

        static::assertCount(1, $tokens);

        $vaultToken = array_pop($tokens);

        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $this->getRequest()
            ->setPostValue([
                'public_hash' => $vaultToken->getPublicHash(),
                'form_key' => $formKey->getFormKey()
            ])
            ->setMethod(Request::METHOD_POST);
        $this->dispatch('vault/cards/deleteaction');
        
        static::assertTrue($this->getResponse()->isRedirect());
        static::assertRedirect(static::stringContains('vault/cards/listaction'));
        static::assertSessionMessages(static::equalTo(['Stored Payment Method was successfully removed']));
        static::assertEmpty($tokenManagement->getCustomerSessionTokens());
    }
}
