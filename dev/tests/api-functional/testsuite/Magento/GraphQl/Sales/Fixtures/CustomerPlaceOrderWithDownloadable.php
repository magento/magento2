<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales\Fixtures;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\TestCase\GraphQl\Client;

class CustomerPlaceOrderWithDownloadable
{
    /**
     * @var Client
     */
    private $gqlClient;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $authHeader;

    /**
     * @var string
     */
    private $cartId;

    /**
     * @var array
     */
    private $customerLogin;

    /**
     * @param Client $gqlClient
     * @param CustomerTokenServiceInterface $tokenService
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Client $gqlClient,
        CustomerTokenServiceInterface $tokenService,
        ProductRepositoryInterface $productRepository
    ) {
        $this->gqlClient = $gqlClient;
        $this->tokenService = $tokenService;
        $this->productRepository = $productRepository;
    }

    /**
     * Place order for a downloadable product
     *
     * @param array $customerLogin
     * @param array $productData
     * @return array
     */
    public function placeOrderWithDownloadableProduct(array $customerLogin, array $productData): array
    {
        $this->customerLogin = $customerLogin;
        $this->createCustomerCart();
        $this->addDownloadableProduct($productData);
        $this->setBillingAddress();
        $paymentMethodCode ='checkmo';
        $this->setPaymentMethod($paymentMethodCode);
        return $this->doPlaceOrder();
    }

    /**
     * Make GraphQl POST request
     *
     * @param string $query
     * @param array $additionalHeaders
     * @return array
     */
    private function makeRequest(string $query, array $additionalHeaders = []): array
    {
        $headers = array_merge([$this->getAuthHeader()], $additionalHeaders);
        return $this->gqlClient->post($query, [], '', $headers);
    }

    /**
     * Get header for authenticated requests
     *
     * @return string
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getAuthHeader(): string
    {
        if (empty($this->authHeader)) {
            $customerToken = $this->tokenService
                ->createCustomerAccessToken($this->customerLogin['email'], $this->customerLogin['password']);
            $this->authHeader = "Authorization: Bearer {$customerToken}";
        }
        return $this->authHeader;
    }

    /**
     * Get cart id
     *
     * @return string
     */
    private function getCartId(): string
    {
        if (empty($this->cartId)) {
            $this->cartId = $this->createCustomerCart();
        }
        return $this->cartId;
    }

    /**
     * Create empty cart for the customer
     *
     * @return array
     */
    private function createCustomerCart(): string
    {
        //Create empty cart
        $createEmptyCart = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $result = $this->makeRequest($createEmptyCart);
        return $result['createEmptyCart'];
    }

    /**
     * Add downloadable product with link to the cart
     *
     * @param array $productData
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function addDownloadableProduct(array $productData)
    {
        $productSku = $productData['sku'];
        $qty = $productData['quantity'] ?? 1;
        /** @var Product $downloadableProduct */
        $downloadableProduct = $this->productRepository->get($productSku);
        /** @var LinkInterface $downloadableProductLinks */
        $downloadableProductLinks = $downloadableProduct->getExtensionAttributes()->getDownloadableProductLinks();
        $linkId = $downloadableProductLinks[0]->getId();

        $addProduct = <<<QUERY
mutation {
    addDownloadableProductsToCart(
        input: {
            cart_id: "{$this->getCartId()}",
            cart_items: [
                {
                    data: {
                        quantity: {$qty},
                        sku: "{$productSku}"
                    },
                    downloadable_product_links: [
                        {
          	                link_id: {$linkId}
                        }
                    ]
                }
            ]
        }
    ) {
        cart {
            items {
                quantity
                ... on DownloadableCartItem {
                    links {
                        title
                        link_type
                        price
                    }
                }
            }
        }
    }
}
QUERY;
        return $this->makeRequest($addProduct);
    }

    /**
     * Set the billing address on the cart
     *
     * @return array
     */
    private function setBillingAddress(): array
    {
        $setBillingAddress = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$this->getCartId()}"
      billing_address: {
         address: {
          firstname: "John"
          lastname: "Smith"
          company: "Test company"
          street: ["test street 1", "test street 2"]
          city: "Texas City"
          postcode: "78717"
          telephone: "5123456677"
          region: "TX"
          country_code: "US"
         }
      }
    }
  ) {
    cart {
      billing_address {
        __typename
      }
    }
  }
}
QUERY;
        return $this->makeRequest($setBillingAddress);
    }

    /**
     * Set the payment method on the cart
     *
     * @param string $paymentMethodCode
     * @return array
     */
    private function setPaymentMethod(string $paymentMethodCode): array
    {
        $setPaymentMethod = <<<QUERY
mutation {
  setPaymentMethodOnCart(
    input: {
      cart_id: "{$this->getCartId()}"
      payment_method: {
        code: "{$paymentMethodCode}"
      }
    }
  ) {
    cart {
      selected_payment_method {
        code
      }
    }
  }
}
QUERY;
        return $this->makeRequest($setPaymentMethod);
    }

    /**
     * Place the order
     *
     * @return array
     */
    private function doPlaceOrder(): array
    {
        $placeOrder = <<<QUERY
mutation {
  placeOrder(
    input: {
      cart_id: "{$this->getCartId()}"
    }
  ) {
    order {
      order_number
    }
  }
}
QUERY;
        return $this->makeRequest($placeOrder);
    }
}
