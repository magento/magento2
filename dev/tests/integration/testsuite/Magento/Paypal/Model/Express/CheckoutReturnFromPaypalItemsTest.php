<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Paypal\Model\Express;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Api\Type\Factory;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Info;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutReturnFromPaypalItemsTest extends TestCase
{
    use MockCreationTrait;

    #[
        AppIsolation(true),
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
    ]
    public function testButtonReturnDoesNotCopyShippingItemsToBilling(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $quote = $objectManager->get(CartRepositoryInterface::class)->get(
            DataFixtureStorageManager::getStorage()->get('cart')->getId()
        );
        $this->assertNotEmpty($quote->getShippingAddress()->getAllItems());

        $exportedKeys = ['firstname', 'lastname', 'street', 'city', 'telephone', 'postcode', 'region_id', 'email'];
        $exportedAddress = new DataObject(
            [
                'firstname' => 'Paypal',
                'lastname' => 'Buyer',
                'street' => 'Street 1',
                'city' => 'Test',
                'telephone' => '11111111',
                'postcode' => '9001',
                'region_id' => '1',
                'email' => 'buyer@example.com',
            ]
        );
        $exportedAddress->setExportedKeys($exportedKeys);

        $api = $this->createPartialMockWithReflection(
            Nvp::class,
            ['call', 'getExportedShippingAddress', 'getExportedBillingAddress', 'getShippingRateCode']
        );
        $api->method('call')->willReturn([]);
        $api->method('getExportedShippingAddress')->willReturn($exportedAddress);
        $api->method('getExportedBillingAddress')->willReturn($exportedAddress);
        $apiTypeFactory = $this->createMock(Factory::class);
        $apiTypeFactory->method('create')->willReturn($api);

        $checkout = $objectManager->create(
            Checkout::class,
            [
                'params' => ['quote' => $quote, 'config' => $this->createMock(Config::class)],
                'apiTypeFactory' => $apiTypeFactory,
                'paypalInfo' => $this->createMock(Info::class),
            ]
        );
        $quote->getPayment()->setMethod(Config::METHOD_WPS_EXPRESS);
        $quote->getPayment()->setAdditionalInformation(Checkout::PAYMENT_INFO_BUTTON, 1);

        $checkout->returnFromPaypal('token');

        $this->assertNotEmpty($quote->getShippingAddress()->getAllItems());
        $this->assertSame([], $quote->getBillingAddress()->getAllItems());
    }
}
