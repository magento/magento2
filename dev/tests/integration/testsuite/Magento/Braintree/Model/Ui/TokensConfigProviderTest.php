<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui;

use Magento\Braintree\Model\Ui\PayPal\ConfigProvider as PayPalConfigProvider;
use Magento\Braintree\Model\Ui\PayPal\TokenUiComponentProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\Ui\TokensConfigProvider;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use PHPUnit\Framework\MockObject_MockObject as MockObject;

/**
 * Class TokensConfigProviderTest
 */
class TokensConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var TokenUiComponentProviderInterface|MockObject
     */
    private $tokenComponentProvider;

    /**
     * @var TokensConfigProvider
     */
    private $configProvider;

    /**
     * @var Bootstrap
     */
    private $bootstrap;

    protected function setUp()
    {
        $this->bootstrap = Bootstrap::getInstance();
        $this->bootstrap->loadArea(Area::AREA_FRONTEND);
        $this->objectManager = Bootstrap::getObjectManager();
        
        $this->tokenComponentProvider = $this->objectManager->get(TokenUiComponentProvider::class);
        
        $this->configProvider = $this->objectManager->create(
            TokensConfigProvider::class,
            [
                'tokenUiComponentProviders' => [
                    PayPalConfigProvider::PAYPAL_CODE => $this->tokenComponentProvider
                ]
            ]
        );
    }

    /**
     * @covers \Magento\Vault\Model\Ui\TokensConfigProvider::getConfig
     * @magentoDataFixture Magento/Braintree/_files/paypal_vault_token.php
     */
    public function testGetConfig()
    {
        $customerId = 1;
        $token = 'mx29vk';
        $payerEmail = 'john.doe@example.com';

        /** @var PaymentTokenManagement $tokenManagement */
        $tokenManagement = $this->objectManager->get(PaymentTokenManagement::class);
        $paymentToken = $tokenManagement->getByGatewayToken($token, PayPalConfigProvider::PAYPAL_CODE, $customerId);
        $item = PayPalConfigProvider::PAYPAL_VAULT_CODE . '_' . $paymentToken->getEntityId();

        /** @var Session $session */
        $session = $this->objectManager->get(Session::class);
        $session->setCustomerId($customerId);

        $actual = $this->configProvider->getConfig()['payment']['vault'];
        static::assertCount(1, $actual);
        static::assertNotEmpty($actual[$item]);
        static::assertEquals(PayPalConfigProvider::PAYPAL_VAULT_CODE, $actual[$item]['config']['code']);
        static::assertEquals($payerEmail, $actual[$item]['config']['details']['payerEmail']);
    }
}
