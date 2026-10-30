<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\ProductFrontendAction;

use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Test\Fixture\CustomerVisitors;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test for \Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SynchronizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /** @var Session */
    private $session;

    /** @var Visitor */
    private $visitor;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->session = $objectManager->get(Session::class);
        $this->visitor = $objectManager->get(Visitor::class);

        $this->synchronizer = $objectManager->get(Synchronizer::class);
        $this->productRepository = $objectManager->get(ProductRepository::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSyncActions(): void
    {
        $actionsType = 'recently_viewed_product';
        $productScope = 'website';
        $scopeId = 1;
        $product1 = $this->productRepository->get('simple');
        $product2 = $this->productRepository->get('simple2');
        $product1Id = $product1->getId();
        $product2Id = $product2->getId();
        $productsData = [
            $productScope . '-' . $scopeId . '-' . $product1Id => [
                'added_at' => '1576582660',
                'product_id' => $product1Id,
            ],
            $productScope . '-' . $scopeId . '-' . $product2Id => [
                'added_at' => '1576587153',
                'product_id' => $product2Id,
            ],
        ];

        $this->synchronizer->syncActions($productsData, $actionsType);

        $synchronizedCollection = $this->synchronizer->getActionsByType($actionsType);
        $synchronizedCollection->addFieldToFilter(
            'product_id',
            [
                $product1Id,
                $product2Id,
            ]
        );

        foreach ($synchronizedCollection as $item) {
            $productScopeId = $productScope . '-' . $scopeId . '-' . $item->getProductId();
            $this->assertArrayHasKey($productScopeId, $productsData);
            $this->assertEquals($productsData[$productScopeId]['added_at'], $item->getAddedAt());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSyncActionsWithoutActionsType(): void
    {
        $product1 = $this->productRepository->get('simple');
        $product2 = $this->productRepository->get('simple2');
        $product1Id = $product1->getId();
        $product2Id = $product2->getId();
        $productsData = [
            $product1Id => [
                'id' => $product1Id,
                'name' => $product1->getName(),
                'type' => $product1->getTypeId(),
            ],
            $product2Id => [
                'id' => $product2Id,
                'name' => $product2->getName(),
                'type' => $product2->getTypeId(),
            ],
        ];

        $this->synchronizer->syncActions($productsData, '');
    }

    /**
     * Tests that product actions are returned correctly according to the provided customer or visitor.
     *
     * @param int|null $visitorId
     * @param string|null $customerEmail
     * @param int $expectedCollectionSize
     * @return void
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    #[DataProvider('getAllActionsDataProvider')]
    public function testGetAllActions(?int $visitorId, ?string $customerEmail, int $expectedCollectionSize): void
    {
        $customerId = $customerEmail ? $this->customerRepository->get($customerEmail)->getId() : null;
        $this->session->setCustomerId($customerId);
        $this->visitor->setId($visitorId);
        $actionsType = 'recently_viewed_product';
        $productScope = 'website';
        $scopeId = 1;
        $product1 = $this->productRepository->get('simple');
        $product2 = $this->productRepository->get('simple2');
        $product1Id = $product1->getId();
        $product2Id = $product2->getId();
        $productsData = [
            $productScope . '-' . $scopeId . '-' . $product1Id => [
                'added_at' => '1576582660',
                'product_id' => $product1Id,
            ],
            $productScope . '-' . $scopeId . '-' . $product2Id => [
                'added_at' => '1576587153',
                'product_id' => $product2Id,
            ],
        ];

        $this->synchronizer->syncActions($productsData, $actionsType);
        $collection = $this->synchronizer->getAllActions();

        $this->assertEquals($expectedCollectionSize, $collection->getSize());
    }

    /**
     * @return array[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public static function getAllActionsDataProvider()
    {
        return [
            ['visitorId' => null, 'customerEmail' => 'customer@example.com', 'expectedCollectionSize' => 2],
            ['visitorId' => 123, 'customerEmail' => null, 'expectedCollectionSize' => 2],
            ['visitorId' => null, 'customerEmail' => null, 'expectedCollectionSize' => 0],
        ];
    }

    #[AppArea('adminhtml')]
    #[AppIsolation(true)]
    #[DataFixture(ProductFixture::class, as: 'product', count: 3)]
    #[DataFixture(CustomerFixture::class, as: 'customer')]
    #[DataFixture(CustomerVisitors::class, ['customer_id' => '$customer.id$', 'count' => 0], as: 'visitors')]
    #[DataProvider('deletedProductsDataProvider')]
    public function testSyncActionsSkipsDeletedProducts(string $typeId, bool $isCustomer): void
    {
        $fixtures = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(DataFixtureStorageManager::class)->getStorage();
        $deletedProduct = $fixtures->get('product1');
        $validProduct = $fixtures->get('product2');
        $unchangedProduct = $fixtures->get('product3');
        $this->session->setCustomerId($isCustomer ? $fixtures->get('customer')->getId() : null);
        $this->visitor->setId($fixtures->get('visitors')->getData('guest_ids')[0]);
        $productsData = [];
        foreach ([$deletedProduct, $validProduct, $unchangedProduct] as $product) {
            $productsData[$product->getId()] = ['product_id' => $product->getId(), 'added_at' => 100];
        }
        $this->synchronizer->syncActions($productsData, $typeId);
        $this->productRepository->delete($deletedProduct);
        $productsData[$validProduct->getId()]['added_at'] = 200;
        unset($productsData[$unchangedProduct->getId()]);

        $this->synchronizer->syncActions($productsData, $typeId);

        $actions = $this->synchronizer->getActionsByType($typeId)->getData();
        $this->assertCount(2, $actions);
        $this->assertEquals(
            [$validProduct->getId() => 200, $unchangedProduct->getId() => 100],
            array_column($actions, 'added_at', 'product_id')
        );

        $this->synchronizer->syncActions(
            [$deletedProduct->getId() => $productsData[$deletedProduct->getId()]],
            $typeId
        );

        $this->assertEquals($actions, $this->synchronizer->getActionsByType($typeId)->getData());
    }

    public static function deletedProductsDataProvider(): array
    {
        return [
            'guest viewed' => ['recently_viewed_product', false],
            'customer viewed' => ['recently_viewed_product', true],
            'guest compared' => ['recently_compared_product', false],
            'customer compared' => ['recently_compared_product', true],
        ];
    }
}
