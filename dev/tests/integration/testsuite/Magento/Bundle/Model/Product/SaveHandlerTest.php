<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Copier;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    
    /**
     * @var Relation
     */
    private $productRelationResourceModel;
    
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->productRelationResourceModel = $this->objectManager->create(Relation::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$'], 'store2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$'], 'store3'),
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle'),
    ]
    public function testOptionTitlesOnDifferentStores(): void
    {
        /** @var OptionList $optionList */
        $optionList = $this->objectManager->create(OptionList::class);

        $secondStoreId = (int)$this->fixtures->get('store2')->getId();
        $thirdStoreId = (int)$this->fixtures->get('store3')->getId();
        $secondStoreCode = $this->fixtures->get('store2')->getCode();
        $thirdStoreCode = $this->fixtures->get('store3')->getCode();
        $sku = $this->fixtures->get('bundle')->getSku();

        $product = $this->productRepository->get($sku, true, $secondStoreId, true);
        $options = $optionList->getItems($product);
        $title = $options[0]->getTitle();
        $newTitle = $title . ' ' . $secondStoreCode;
        $options[0]->setTitle($newTitle);
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();

        $product = $this->productRepository->get($sku, true, $thirdStoreId, true);
        $options = $optionList->getItems($product);
        $newTitle = $title . ' ' . $thirdStoreCode;
        $options[0]->setTitle($newTitle);
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();

        $product = $this->productRepository->get($sku, false, $secondStoreId, true);
        $options = $optionList->getItems($product);
        $this->assertCount(1, $options);
        $this->assertEquals(
            $title . ' ' . $secondStoreCode,
            $options[0]->getTitle()
        );
    }

    #[
        DbIsolation(false),
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$', '$p2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bundle'),
    ]
    public function testOptionLinksOfSameProduct(): void
    {
        $bundleSku = $this->fixtures->get('bundle')->getSku();
        $bundleProduct = $this->productRepository->get($bundleSku, true, 0, true);
        $extension = $bundleProduct->getExtensionAttributes();
        $options = $extension->getBundleProductOptions();
        $this->assertCount(2, $options);
        $this->assertTrue($bundleProduct->isSalable());

        //remove one option and verify the count
        array_pop($options);
        $extension->setBundleProductOptions($options);
        $bundleProduct->setExtensionAttributes($extension);
        $this->productRepository->save($bundleProduct);
        
        // reload product and verify only one option is left and product is salable
        $bundleProduct = $this->productRepository->get($bundleSku, true, 0, true);
        $extension = $bundleProduct->getExtensionAttributes();
        $options = $extension->getBundleProductOptions();
        $this->assertCount(1, $options);
        $this->assertTrue($bundleProduct->isSalable());

        // check that p1 is still related to bundle product as the other option includes it
        $simpleProduct1 = $this->fixtures->get('p1');
        $parentIds = $this->productRelationResourceModel->getRelationsByChildren([$simpleProduct1->getId()]);
        $this->assertContains($bundleProduct->getId(), $parentIds[$simpleProduct1->getId()]);
        // check that p2 is not related to bundle product as the option including it was removed
        $simpleProduct2 = $this->fixtures->get('p2');
        $parentIds = $this->productRelationResourceModel->getRelationsByChildren([$simpleProduct2->getId()]);
        $this->assertNotContains($bundleProduct->getId(), $parentIds[$simpleProduct2->getId()] ?? []);
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bundle'),
    ]
    public function testRemoveBundleOptionAfterDuplicate(): void
    {
        $bundleSku = $this->fixtures->get('bundle')->getSku();
        $bundleProduct = $this->productRepository->get($bundleSku, true, 0, true);
        $extension = $bundleProduct->getExtensionAttributes();
        $options = $extension->getBundleProductOptions();
        $this->assertCount(2, $options);

        // Duplicate the bundle product
        $copier = $this->objectManager->create(Copier::class);
        $duplicateBundleProduct = $copier->copy($bundleProduct);

        // Remove the second option from the original bundle product
        $bundleProduct = $this->productRepository->get($bundleSku, true, forceReload:  true);
        $extension = $bundleProduct->getExtensionAttributes();
        $extension->setBundleProductOptions([$options[0]]);
        $bundleProduct->setExtensionAttributes($extension);
        $this->productRepository->save($bundleProduct);
        
        // Check that the original bundle product has only one option left
        $options = $this->getBundleOptions($bundleSku);
        $this->assertCount(1, $options);
        $this->assertCount(1, $options[0]->getProductLinks());
        $this->assertEquals($this->fixtures->get('p1')->getSku(), $options[0]->getProductLinks()[0]->getSku());
        
        // Check that the duplicated bundle product still has both options
        $options = $this->getBundleOptions($duplicateBundleProduct->getSku());
        $this->assertCount(2, $options);
        $this->assertCount(1, $options[0]->getProductLinks());
        $this->assertEquals($this->fixtures->get('p1')->getSku(), $options[0]->getProductLinks()[0]->getSku());
        $this->assertCount(1, $options[1]->getProductLinks());
        $this->assertEquals($this->fixtures->get('p2')->getSku(), $options[1]->getProductLinks()[0]->getSku());
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bundle'),
    ]
    public function testRemoveBundleOptionFromDuplicate(): void
    {
        $bundleSku = $this->fixtures->get('bundle')->getSku();
        $bundleProduct = $this->productRepository->get($bundleSku, true, 0, true);
        $extension = $bundleProduct->getExtensionAttributes();
        $options = $extension->getBundleProductOptions();
        $this->assertCount(2, $options);

        // Duplicate the bundle product
        $copier = $this->objectManager->create(Copier::class);
        $duplicateBundleProduct = $copier->copy($bundleProduct);

        // Remove the second option from the duplicated bundle product
        $bundleProduct = $this->productRepository->get($duplicateBundleProduct->getSku(), true, forceReload:  true);
        $extension = $bundleProduct->getExtensionAttributes();
        $options = $extension->getBundleProductOptions();
        $extension->setBundleProductOptions([$options[0]]);
        $bundleProduct->setExtensionAttributes($extension);
        $this->productRepository->save($bundleProduct);

        // Check that the original bundle product still has both options
        $options = $this->getBundleOptions($bundleSku);
        $this->assertCount(2, $options);
        $this->assertCount(1, $options[0]->getProductLinks());
        $this->assertEquals($this->fixtures->get('p1')->getSku(), $options[0]->getProductLinks()[0]->getSku());
        $this->assertCount(1, $options[1]->getProductLinks());
        $this->assertEquals($this->fixtures->get('p2')->getSku(), $options[1]->getProductLinks()[0]->getSku());

        // Check that the duplicated bundle product has only one option left
        $options = $this->getBundleOptions($duplicateBundleProduct->getSku());
        $this->assertCount(1, $options);
        $this->assertCount(1, $options[0]->getProductLinks());
        $this->assertEquals($this->fixtures->get('p1')->getSku(), $options[0]->getProductLinks()[0]->getSku());
    }

    private function getBundleOptions(string $bundleSku): array
    {
        $bundleProduct = $this->productRepository->get($bundleSku, true, forceReload: true);
        return $bundleProduct?->getExtensionAttributes()?->getBundleProductOptions() ?? [];
    }
}
