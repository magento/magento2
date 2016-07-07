<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block;

use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\PaymentTokens;

/**
 * Class VaultTokenRendererTest
 */
class VaultTokenRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentTokens
     */
    private $tokenBlock;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    
    protected function setUp()
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->loadArea(Area::AREA_FRONTEND);
        $this->objectManager = Bootstrap::getObjectManager();
        
        $this->tokenBlock = $this->objectManager->get(PaymentTokens::class);
    }

    /**
     * @covers \Magento\Vault\Block\CreditCards::getPaymentTokens
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
        static::assertEquals(PaymentTokenInterface::TOKEN_TYPE, $vaultToken->getType());
    }
}
