<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\InstantPurchase\Model\InstantPurchaseOption;
use Magento\InstantPurchase\Model\InstantPurchaseOptionLoadingFactory;
use Magento\InstantPurchase\Model\PlaceOrder;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class OutOfStockItemSaveTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /** @var ProductRepositoryInterface $productRepository */
    private $productRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDbIsolation disabled
     */
    public function testSaveWithZeroQuantityAndInventoryCheckDisabled()
    {

        /** @var ProductInterface $product */
        $product = $this->productRepository->get('simple', false, null, true);

        /** @var ProductExtensionInterface $ea */
        $ea = $product->getExtensionAttributes();
        $this->productRepository->save($product);

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(0);
        $stockItem->setIsInStock(0);
        /** @var StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $this->objectManager->get(StockItemRepositoryInterface::class);
        $stockItemRepository->save($stockItem);

        $this->expectExceptionObject(
            new LocalizedException(__('This product is out of stock.'))
        );
        $this->invokeTestProductPlacement($product->getSku(),[]);
    }


    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDbIsolation disabled
     */
    public function testSaveWithPositiveQuantityAndInventoryCheckDisabled()
    {
        /** @var ProductInterface $product */
        $product = $this->productRepository->get('simple', false, null, true);

        /** @var ProductExtensionInterface $ea */
        $ea = $product->getExtensionAttributes();
        $this->productRepository->save($product);

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(100);
        $stockItem->setIsInStock(0);
        /** @var StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $this->objectManager->get(StockItemRepositoryInterface::class);
        $stockItemRepository->save($stockItem);

        $this->expectExceptionObject(
            new LocalizedException(__('This product is out of stock.'))
        );
        $this->invokeTestProductPlacement($product->getSku(),[]);
    }


    /**
     * Run system under test.
     *
     * @param $productSku
     * @param array $productRequest
     * @param string $expectedResult
     * @return int order identifier
     */
    private function invokeTestProductPlacement($productSku, array $productRequest)
    {
        /** @var PlaceOrder $model */
        $model = $this->objectManager->create(PlaceOrder::class);

        $store = $this->getFixtureStore();
        $customer = $this->getFixtureCustomer();
        $instantPurchaseOption = $this->createInstantPurchaseOptionFromFixture();
        $product = $this->getFixtureProduct($productSku);

        $model->placeOrder(
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
