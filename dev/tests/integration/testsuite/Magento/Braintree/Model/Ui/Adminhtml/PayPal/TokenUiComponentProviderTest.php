<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui\Adminhtml\PayPal;

use Magento\Braintree\Model\Ui\Adminhtml\PayPal\TokenUiComponentProvider;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

/**
 * Contains tests for PayPal token Ui component provider
 */
class TokenUiComponentProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var TokenUiComponentProvider
     */
    private $tokenComponentProvider;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->tokenComponentProvider = $this->objectManager->get(TokenUiComponentProvider::class);
    }

    /**
     * @covers \Magento\Braintree\Model\Ui\Adminhtml\PayPal\TokenUiComponentProvider::getComponentForToken
     * @magentoDataFixture Magento/Braintree/_files/paypal_vault_token.php
     * @magentoAppArea adminhtml
     */
    public function testGetComponentForToken()
    {
        $customerId = 1;
        $token = 'mx29vk';
        $payerEmail = 'john.doe@example.com';

        /** @var PaymentTokenManagement $tokenManagement */
        $tokenManagement = $this->objectManager->get(PaymentTokenManagement::class);
        $paymentToken = $tokenManagement->getByGatewayToken($token, ConfigProvider::PAYPAL_CODE, $customerId);

        $component = $this->tokenComponentProvider->getComponentForToken($paymentToken);
        $config = $component->getConfig();

        static::assertNotEmpty($config[TokenUiComponentProviderInterface::COMPONENT_DETAILS]);
        static::assertNotEmpty($config[TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH]);
        static::assertEquals(ConfigProvider::PAYPAL_VAULT_CODE, $config['code']);

        $details = $config[TokenUiComponentProviderInterface::COMPONENT_DETAILS];
        static::assertEquals($payerEmail, $details['payerEmail']);
        static::assertNotEmpty($details['icon']);
    }
}
