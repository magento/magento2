<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Framework\Registry;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Allow guest checkout option test
 */
class AllowGuestCheckoutOptionTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->orderCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
    }

    /**
     * @magentoConfigFixture default_store checkout/options/guest_checkout 0
     */
    public function testCreateEmptyCartIfGuestCheckoutIsDisabled()
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
        self::assertEquals('default', $guestCart->getStore()->getCode());
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoConfigFixture default_store checkout/options/guest_checkout 0
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Guest checkout is not allowed. Register a customer account or login with existing one.
     */
    public function testSetBillingAddressToGuestCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "test region"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
          save_in_address_book: false
        }
      }
    }
  ) {
    cart {
      billing_address {
        city
      }
    }
  }
}
QUERY;

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoConfigFixture default_store checkout/options/guest_checkout 0
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Guest checkout is not allowed. Register a customer account or login with existing one.
     */
    public function testSetGuestEmailOnCartWithGuestCheckoutDisabled()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $email = 'some@user.com';

        $query = <<<QUERY
mutation {
  setGuestEmailOnCart(input: {
    cart_id: "$maskedQuoteId"
    email: "$email"
  }) {
    cart {
      email
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoConfigFixture default_store checkout/options/guest_checkout 0
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Guest checkout is not allowed. Register a customer account or login with existing one.
     */
    public function testSetPaymentOnCartWithGuestCheckoutDisabled()
    {
        $methodCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setPaymentMethodOnCart(input: {
    cart_id: "{$maskedQuoteId}", 
    payment_method: {
      code: "{$methodCode}"
    }
  }) {
    cart {
      selected_payment_method {
        code
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('setPaymentMethodOnCart', $response);
        self::assertArrayHasKey('cart', $response['setPaymentMethodOnCart']);
        self::assertArrayHasKey('selected_payment_method', $response['setPaymentMethodOnCart']['cart']);
        self::assertEquals($methodCode, $response['setPaymentMethodOnCart']['cart']['selected_payment_method']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoConfigFixture default_store checkout/options/guest_checkout 0
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Guest checkout is not allowed. Register a customer account or login with existing one.
     */
    public function testSetNewShippingAddressOnCartWithGuestCheckoutDisabled()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          address: {
            firstname: "test firstname"
            lastname: "test lastname"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "test region"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: false
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        firstname
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoConfigFixture default_store checkout/options/guest_checkout 0
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Guest checkout is not allowed. Register a customer account or login with existing one.
     */
    public function testSetShippingMethodOnCartWithGuestCheckoutDisabled()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $carrierCode = 'flatrate';
        $methodCode = 'flatrate';

        $query = <<<QUERY
mutation {
  setShippingMethodsOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      shipping_methods: [{
        carrier_code: "$carrierCode"
        method_code: "$methodCode"
      }]
    }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
        }
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/tablerate/active 1
     * @magentoConfigFixture default_store carriers/freeshipping/active 1
     * @magentoConfigFixture default_store payment/banktransfer/active 1
     * @magentoConfigFixture default_store payment/cashondelivery/active 1
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoConfigFixture default_store payment/purchaseorder/active 1
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/active 1
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     * @magentoConfigFixture default_store checkout/options/guest_checkout 0
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Guest checkout is not allowed. Register a customer account or login with existing one.
     */
    public function testPlaceOrderWithGuestCheckoutDisabled()
    {
        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $query = $this->getQuery($maskedQuoteId);
        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
    order {
      order_id
    }
  }
}
QUERY;
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        $orderCollection = $this->orderCollectionFactory->create();
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }
}
