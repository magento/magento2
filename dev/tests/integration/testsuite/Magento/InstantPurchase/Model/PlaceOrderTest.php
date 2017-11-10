<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class PlaceOrderTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testPlaceOrderForSimpleProduct()
    {
        $this->invokeTestProductPlacement(
            'simple',
            [],
            'Simple Product Ordered.'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testPlaceOrderForConfigurableProduct()
    {
        $configurable = $this->getFixtureProduct('configurable');
        $simple = $this->getFixtureProduct('simple_10');
        $configurableAttributes = $configurable->getTypeInstance()->getConfigurableAttributes($configurable);
        $selectedOptions = [];
        foreach ($configurableAttributes as $configurableAttribute) {
            $configurableAttributeId = $configurableAttribute->getData('attribute_id');
            $configurableAttributeCode = $configurableAttribute->getProductAttribute()->getData('attribute_code');
            $configurableAttributeValueInSimple = $simple->getCustomAttribute($configurableAttributeCode)->getValue();
            $selectedOptions[$configurableAttributeId] = $configurableAttributeValueInSimple;
        }

        $this->invokeTestProductPlacement(
            $configurable->getSku(),
            [
                'super_attribute' => $selectedOptions,
            ],
            'Configurable Product Ordered.'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testPlaceOrderForGroupedProduct()
    {
        $grouped = $this->getFixtureProduct('grouped-product');
        $selectedQuantities = [];
        foreach ($grouped->getTypeInstance()->getAssociatedProductIds($grouped) as $associatedProductId) {
            $selectedQuantities[$associatedProductId] = rand(1, 3);
        }

        $this->invokeTestProductPlacement(
            $grouped->getSku(),
            [
                'super_group' => $selectedQuantities,
            ],
            'Grouped Product Ordered.'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testPlaceOrderForDownloadableProduct()
    {
        $downloadable = $this->getFixtureProduct('downloadable-product');
        $selectedLinks = [];
        foreach ($downloadable->getTypeInstance()->getLinks($downloadable) as $link) {
            $selectedLinks[] = $link->getId();
        }

        $this->invokeTestProductPlacement(
            $downloadable->getSku(),
            [
                'links' => $selectedLinks
            ],
            'Downloadable Product Ordered.'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     */
    public function testPlaceOrderForVirtualProduct()
    {
        $this->invokeTestProductPlacement(
            'virtual-product',
            [],
            'Virtual Product Ordered.'
        );
    }

    /**
     * Run system under test.
     *
     * @param $productSku
     * @param array $productRequest
     * @param string $expectedResult
     * @return int order identifier
     */
    private function invokeTestProductPlacement($productSku, array $productRequest, string $expectedResult): int
    {
        /** @var PlaceOrder $model */
        $model = $this->objectManager->create(PlaceOrder::class);

        $store = $this->getFixtureStore();
        $customer = $this->getFixtureCustomer();
        $instantPurchaseOption = $this->createInstantPurchaseOptionFromFixture();
        $product = $this->getFixtureProduct($productSku);

        $orderId = $model->placeOrder(
            $store,
            $customer,
            $instantPurchaseOption,
            $product,
            array_merge(
                [
                    'qty' => '1',
                    'options' => $this->createProductOptionsRequest($product)
                ],
                $productRequest
            )
        );
        $this->assertNotEmpty($orderId, $expectedResult);
        return $orderId;
    }

    /**
     * Returns Store created by fixture.
     *
     * @return Store
     */
    private function getFixtureStore(): Store
    {
        $repository = $this->objectManager->create(StoreRepositoryInterface::class);
        $store = $repository->get('default');
        return $store;
    }

    /**
     * Returns Customer created by fixture.
     *
     * @return Customer
     */
    private function getFixtureCustomer(): Customer
    {
        $repository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customerData = $repository->getById(1);
        $customer = $this->objectManager->create(Customer::class);
        $customer->updateData($customerData);
        return $customer;
    }

    /**
     * Returns Product created by fixture.
     *
     * @param string $sku
     * @return Product
     */
    private function getFixtureProduct(string $sku): Product
    {
        $repository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $repository->get($sku, false, $this->getFixtureStore()->getId());
        $product->setData('salable', true);
        return $product;
    }

    /**
     * Creates instant purchase option based on data from fixture.
     *
     * @return InstantPurchaseOption
     */
    private function createInstantPurchaseOptionFromFixture(): InstantPurchaseOption
    {
        $factory = $this->objectManager->get(InstantPurchaseOptionLoadingFactory::class);
        $fixtureCustomer = $this->getFixtureCustomer();
        $option = $factory->create(
            $fixtureCustomer->getId(),
            'fakePublicHash', // @see Magento/InstantPurchase/_files/fake_payment_token.php
            $fixtureCustomer->getDefaultShippingAddress()->getId(),
            $fixtureCustomer->getDefaultBillingAddress()->getId(),
            'instant-purchase',
            'cheapest'
        );
        return $option;
    }

    /**
     * Creates custom options selection product request data.
     *
     * @param Product $product
     * @return array
     */
    private function createProductOptionsRequest(Product $product): array
    {
        $options = [];
        /** @var Product\Option $option */
        foreach ($product->getOptions() as $option) {
            switch ($option->getGroupByType()) {
                case ProductCustomOptionInterface::OPTION_GROUP_DATE:
                    $value = [
                        'year' => date('Y'),
                        'month' => date('n'),
                        'day' => date('j'),
                        'hour' => date('G'),
                        'minute' => date('i')
                    ];
                    break;
                case ProductCustomOptionInterface::OPTION_GROUP_SELECT:
                    $value = key($option->getValues());
                    break;
                default:
                    $value = 'test';
                    break;
            }
            $options[$option->getId()] = $value;
        }
        return $options;
    }
}
