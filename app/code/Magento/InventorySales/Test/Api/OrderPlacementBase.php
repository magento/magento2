<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Base class for order placement.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class OrderPlacementBase extends WebapiAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Registered customer token.
     *
     * @var string
     */
    private $customerToken;

    /**
     * Registered or guest customer cart id.
     *
     * @var string
     */
    private $cartId;

    /**
     * Store code to make request to specific website.
     *
     * @var string
     */
    private $storeViewCode = 'default';

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Set store view for test.
     *
     * @param $storeViewCode
     */
    public function setStoreView($storeViewCode)
    {
        $this->storeViewCode = $storeViewCode;
    }

    /**
     * Retrieve registered customer token.
     *
     * @param string $customerEmail
     * @param string $customerPassword
     * @return string
     */
    public function getCustomerToken(string $customerEmail, string $customerPassword): string
    {
        if (!$this->customerToken) {
            $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
            $this->customerToken = $customerTokenService->createCustomerAccessToken($customerEmail, $customerPassword);
        }

        return $this->customerToken;
    }

    /**
     * Get customer empty cart.
     *
     * @return void
     */
    public function createCustomerCart(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];

        if ($this->customerToken) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => '/V1/carts/mine',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                    'token' => $this->customerToken
                ],
            ];

        }

        $this->cartId = (string)$this->_webApiCall($serviceInfo, [], null, $this->storeViewCode);
    }

    /**
     * Add simple, virtual or downloadable product to cart.
     *
     * @param string $sku
     * @param int $qty
     * @return void
     */
    public function addProduct(string $sku, $qty = 1): void
    {
        $serviceInfo = $this->getAddProductServiceInfo();

        $product = [
            'cartItem' => [
                'sku' => $sku,
                'qty' => $qty,
                'quote_id' => $this->cartId,
            ],
        ];
        $this->_webApiCall($serviceInfo, $product, null, $this->storeViewCode);
    }

    /**
     * Add configurable product to cart.
     *
     * @param string $sku
     * @param int $qty
     * @return void
     */
    public function addConfigurableProduct(string $sku, int $qty = 1): void
    {
        $serviceInfo = $this->getAddProductServiceInfo();
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku, false, $this->storeViewCode);
        $configurableProductOptions = $product->getExtensionAttributes()->getConfigurableProductOptions();
        $attributeId = $configurableProductOptions[0]->getAttributeId();
        $options = $configurableProductOptions[0]->getOptions();
        $optionId = $options[0]['value_index'];

        $configurableProduct = [
            'cartItem' => [
                'sku' => $sku,
                'qty' => $qty,
                'quote_id' => $this->cartId,
                'product_option' => [
                    'extension_attributes' => [
                        'configurable_item_options' => [
                            [
                                'option_id' => $attributeId,
                                'option_value' => $optionId,
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->_webApiCall($serviceInfo, $configurableProduct, null, $this->storeViewCode);
    }

    /**
     * Add bundle product to cart.
     *
     * @param string $sku
     * @param int $qty
     * @return void
     */
    public function addBundleProduct(string $sku, int $qty = 1): void
    {
        $serviceInfo = $this->getAddProductServiceInfo();
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku, false, $this->storeViewCode);
        $bundleProductOption = $product->getExtensionAttributes()->getBundleProductOptions()[0];
        $bundleProduct = [
            'cartItem' => [
                'sku' => $sku,
                'qty' => $qty,
                'quote_id' => $this->cartId,
                'product_option' => [
                    'extension_attributes' => [
                        'bundle_options' => [
                            [
                                'option_id' => $bundleProductOption->getId(),
                                'option_qty' => 2,
                                'option_selections' => [0 => $bundleProductOption->getId()]
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->_webApiCall($serviceInfo, $bundleProduct, null, $this->storeViewCode);
    }

    /**
     * Get service info for add product to cart.
     *
     * @return array
     */
    private function getAddProductServiceInfo(): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $this->cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],

        ];
        if ($this->customerToken) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => '/V1/carts/mine/items',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                    'token' => $this->customerToken
                ],

            ];
        }

        return $serviceInfo;
    }

    /**
     * Estimate shipping costs for given customer cart.
     *
     * @return void
     */
    public function estimateShippingCosts(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $this->cartId . '/estimate-shipping-methods',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $this->customerToken
            ],
        ];

        if ($this->customerToken) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => '/V1/carts/mine/estimate-shipping-methods',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                    'token' => $this->customerToken
                ],
            ];
        }

        $body = [
            'address' => [
                'region' => 'California',
                'region_id' => 12,
                'region_code' => 'CA',
                'country_id' => 'US',
                'street' => ['6161 West Centinela Avenue'],
                'postcode' => '90230',
                'city' => 'Culver City',
                'firstname' => 'John',
                'lastname' => 'Smith',
                'customer_id' => 1,
                'email' => 'customer@example.com',
                'telephone' => '(555) 555-5555',
                'same_as_billing' => 1,
            ]
        ];
        $this->_webApiCall($serviceInfo, $body, null, $this->storeViewCode);
    }

    /**
     * Set shipping and billing information for given customer cart.
     *
     * @return void
     */
    public function setShippingAndBillingInformation(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $this->cartId . '/shipping-information',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        if ($this->customerToken) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => '/V1/carts/mine/shipping-information',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                    'token' => $this->customerToken
                ],
            ];
        }

        $body = [
            'addressInformation' => [
                'shipping_address' => [
                    'region' => 'California',
                    'region_id' => 12,
                    'region_code' => 'CA',
                    'country_id' => 'US',
                    'street' => [
                        0 => '6161 West Centinela Avenue',
                    ],
                    'postcode' => '90230',
                    'city' => 'Culver City',
                    'firstname' => 'John',
                    'lastname' => 'Smith',
                    'email' => 'customer@example.com',
                    'telephone' => '(555) 555-5555',
                ],
                'billing_address' => [
                    'region' => 'California',
                    'region_id' => 12,
                    'region_code' => 'CA',
                    'country_id' => 'US',
                    'street' => [
                        0 => '6161 West Centinela Avenue',
                    ],
                    'postcode' => '90230',
                    'city' => 'Culver City',
                    'firstname' => 'John',
                    'lastname' => 'Smith',
                    'email' => 'customer@example.com',
                    'telephone' => '(555) 555-5555',
                ],
                'shipping_carrier_code' => 'flatrate',
                'shipping_method_code' => 'flatrate',
            ],
        ];
        $this->_webApiCall($serviceInfo, $body, null, $this->storeViewCode);
    }

    /**
     * Submit payment information for given customer cart.
     *
     * @return int
     */
    public function submitPaymentInformation(): int
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $this->cartId . '/payment-information',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        if ($this->customerToken) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => '/V1/carts/mine/payment-information',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                    'token' => $this->customerToken
                ],
            ];
        }

        $body = [
            'email' => 'customer@example.com',
            'paymentMethod' => ['method' => 'checkmo'],
            'billing_address' => [
                'email' => 'customer@example.com',
                'region' => 'California',
                'region_id' => 12,
                'region_code' => 'CA',
                'country_id' => 'US',
                'street' => ['6161 West Centinela Avenue'],
                'postcode' => '90230',
                'city' => 'Culver City',
                'telephone' => '(555) 555-5555',
                'firstname' => 'John',
                'lastname' => 'Smith'
            ]
        ];

        return (int)$this->_webApiCall($serviceInfo, $body, null, $this->storeViewCode);
    }

    /**
     * Assign customer to additional website.
     *
     * @param string $customerEmail
     * @param string $websiteCode
     * @return void
     */
    public function assignCustomerToCustomWebsite(string $customerEmail, string $websiteCode): void
    {
        $websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $websiteId = $websiteRepository->get($websiteCode)->getId();
        $customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get($customerEmail);
        $customer->setWebsiteId($websiteId);
        $customerRepository->save($customer);
    }

    /**
     * Assign test products to additional website.
     *
     * @param array $skus
     * @param string $websiteCode
     * @return void
     */
    public function assignProductsToWebsite(array $skus, string $websiteCode): void
    {
        $websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $websiteId = $websiteRepository->get($websiteCode)->getId();
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(ProductInterface::SKU, $skus, 'in')->create();
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $products = $productRepository->getList($searchCriteria)->getItems();

        foreach ($products as $product) {
            $product->setWebsiteIds([$websiteId]);
            $productRepository->save($product);
        }
    }

    /**
     * Retrieve order by id.
     *
     * @param int $orderId
     * @return array
     */
    public function getOrder(int $orderId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders/' . $orderId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        return $this->_webApiCall($serviceInfo, [], null, $this->storeViewCode);
    }

    /**
     * Assign given stock to given website.
     *
     * @param int $stockId
     * @param string $websiteCode
     * @return void
     */
    public function assignStockToWebsite(int $stockId, string $websiteCode): void
    {
        $stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $salesChannelFactory = Bootstrap::getObjectManager()->get(SalesChannelInterfaceFactory::class);
        $stock = $stockRepository->get($stockId);
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();

        $salesChannel = $salesChannelFactory->create();
        $salesChannel->setCode($websiteCode);
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
        $salesChannels[] = $salesChannel;

        $extensionAttributes->setSalesChannels($salesChannels);
        $stockRepository->save($stock);
    }
}
