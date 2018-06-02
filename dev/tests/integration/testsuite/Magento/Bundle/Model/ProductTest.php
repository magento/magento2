<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * As far none class is present as separate bundle product,
 * this test is clone of \Magento\Catalog\Model\Product with product type "bundle"
 */
namespace Magento\Bundle\Model;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Entity;
use Magento\TestFramework\Helper\Bootstrap;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Product
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->model = $this->objectManager->create(Product::class);
        $this->model->setTypeId(Type::TYPE_BUNDLE);
    }

    public function testGetSetTypeInstance()
    {
        // model getter
        $typeInstance = $this->model->getTypeInstance();
        $this->assertInstanceOf(BundleType::class, $typeInstance);
        $this->assertSame($typeInstance, $this->model->getTypeInstance());

        // singleton getter
        $otherProduct = $this->objectManager->create(Product::class);
        $otherProduct->setTypeId(Type::TYPE_BUNDLE);
        $this->assertSame($typeInstance, $otherProduct->getTypeInstance());

        // model setter
        $customTypeInstance = $this->objectManager->create(BundleType::class);
        $this->model->setTypeInstance($customTypeInstance);
        $this->assertSame($customTypeInstance, $this->model->getTypeInstance());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testCRUD()
    {
        $this->model->setTypeId(Type::TYPE_BUNDLE)
            ->setAttributeSetId(4)
            ->setName('Bundle Product')
            ->setSku(uniqid())
            ->setPrice(10)
            ->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);
        $crud = new Entity($this->model, ['sku' => uniqid()]);
        $crud->testCrud();
    }

    public function testGetPriceModel()
    {
        $this->model->setTypeId(Type::TYPE_BUNDLE);
        $type = $this->model->getPriceModel();
        $this->assertInstanceOf(Price::class, $type);
        $this->assertSame($type, $this->model->getPriceModel());
    }

    public function testIsComposite()
    {
        $this->assertTrue($this->model->isComposite());
    }

    /**
     * Checks a case when bundle product is should be available per multiple stores.
     *
     * @magentoDataFixture Magento/Bundle/_files/product_with_multiple_options.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDbIsolation disabled
     */
    public function testMultipleStores()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $bundle = $productRepository->get('bundle-product');

        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $store = $storeRepository->get('fixture_second_store');

        self::assertNotEquals($store->getId(), $bundle->getStoreId());

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore($store->getId());

        $bundle->setStoreId($store->getId())
            ->setCopyFromView(true);
        $updatedBundle = $productRepository->save($bundle);

        self::assertEquals($store->getId(), $updatedBundle->getStoreId());
    }
}
