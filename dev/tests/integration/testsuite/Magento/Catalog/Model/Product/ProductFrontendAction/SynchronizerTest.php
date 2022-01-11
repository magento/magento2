<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\ProductFrontendAction;

use Magento\Catalog\Model\ProductRepository;

/**
 * Test for \Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer.
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->session = $objectManager->get(\Magento\Customer\Model\Session::class);
        $this->visitor = $objectManager->get(\Magento\Customer\Model\Visitor::class);

        $this->synchronizer = $objectManager->get(Synchronizer::class);
        $this->productRepository = $objectManager->get(ProductRepository::class);
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
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetAllActionsWithoutCustomerAndVisitor(): void
    {
        $collection = $this->synchronizer->getAllActions();
        $this->assertEquals($collection->getSize(), 0);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetAllActionsOfVisitor(): void
    {
        $this->visitor->setId(123);
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
        $this->assertEquals($collection->getSize(), 2);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetAllActionsOfCustomer(): void
    {
        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Customer::class);
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer
            ->setWebsiteId(1)
            ->setId(1)
            ->setEntityTypeId(1)
            ->setAttributeSetId(1)
            ->setEmail('customer@example.com')
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setDefaultBilling(1)
            ->setDefaultShipping(1);
        $customer->isObjectNew(true);
        $customer->save();

        $this->session->setCustomerId(1);
        $this->visitor->setId(null);
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
        $this->assertEquals($collection->getSize(), 2);
    }
}
