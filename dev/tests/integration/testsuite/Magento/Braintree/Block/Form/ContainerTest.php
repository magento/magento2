<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Form;

use Magento\Braintree\Model\Ui\PayPal\ConfigProvider as PayPalConfigProvider;
use Magento\Framework\App\Area;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Block\Form\Container;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ContainerTest
 */
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Bootstrap
     */
    private $bootstrap;

    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        $this->bootstrap = Bootstrap::getInstance();
        $this->bootstrap->loadArea(Area::AREA_ADMINHTML);
        $this->objectManager = Bootstrap::getObjectManager();

        $this->container = $this->objectManager->create(Container::class);
    }

    /**
     * @covers \Magento\Payment\Block\Form\Container::getMethods
     * @magentoDataFixture Magento/Braintree/_files/paypal_quote.php
     */
    public function testGetMethods()
    {
        $customerId = 1;
        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $quote = $quoteRepository->getForCustomer($customerId);
        $this->container->setData('quote', $quote);
        $actual = $this->container->getMethods();
        /** @var MethodInterface $paymentMethod */
        foreach ($actual as $paymentMethod) {
            static::assertNotContains($paymentMethod->getCode(), [
                PayPalConfigProvider::PAYPAL_VAULT_CODE, PayPalConfigProvider::PAYPAL_CODE
            ]);
        }
    }
}
