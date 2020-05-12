<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block;

use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\Customer\AccountTokens;
use Magento\Vault\Model\AccountPaymentTokenFactory;

/**
 * Class VaultTokenRendererTest
 */
class VaultTokenRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AccountTokens
     */
    private $tokenBlock;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    
    protected function setUp(): void
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->loadArea(Area::AREA_FRONTEND);
        $this->objectManager = Bootstrap::getObjectManager();
        
        $this->tokenBlock = $this->objectManager->get(AccountTokens::class);
    }

    /**
     * @covers \Magento\Vault\Block\Customer\AccountTokens::getPaymentTokens
     * @magentoDataFixture Magento/Braintree/_files/paypal_vault_token.php
     */
    public function testGetPaymentTokens()
    {
        $customerId = 1;
        $token = 'mx29vk';

        /** @var Session $session */
        $session = $this->objectManager->get(Session::class);
        $session->setCustomerId($customerId);

        $tokens = $this->tokenBlock->getPaymentTokens();

        static::assertCount(1, $tokens);

        /** @var PaymentTokenInterface $vaultToken */
        $vaultToken = array_pop($tokens);

        static::assertTrue($vaultToken->getIsActive());
        static::assertTrue($vaultToken->getIsVisible());
        static::assertEquals($token, $vaultToken->getGatewayToken());
        static::assertEquals(ConfigProvider::PAYPAL_CODE, $vaultToken->getPaymentMethodCode());
        static::assertEquals(AccountPaymentTokenFactory::TOKEN_TYPE_ACCOUNT, $vaultToken->getType());
    }
}
