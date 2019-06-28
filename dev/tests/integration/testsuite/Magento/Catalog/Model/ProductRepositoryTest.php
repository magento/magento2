<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Backend\Model\Auth;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Bootstrap as TestBootstrap;
use Magento\Framework\Acl\Builder;

/**
 * Provide tests for ProductRepository model.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test subject.
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /*
     * @var Auth
     */
    private $auth;

    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * Sets up common objects
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->auth = Bootstrap::getObjectManager()->get(Auth::class);
        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
        $this->productFactory = Bootstrap::getObjectManager()->get(ProductFactory::class);
        $this->productResource = Bootstrap::getObjectManager()->get(ProductResource::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->auth->logout();
        $this->aclBuilder->resetRuntimeAcl();
    }

    /**
     * Checks filtering by store_id
     *
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     */
    public function testFilterByStoreId()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', '1', 'eq')
            ->create();
        $list = $this->productRepository->getList($searchCriteria);
        $count = $list->getTotalCount();

        $this->assertGreaterThanOrEqual(1, $count);
    }

    /**
     * Check a case when product should be retrieved with different SKU variations.
     *
     * @param string $sku
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider skuDataProvider
     */
    public function testGetProduct(string $sku) : void
    {
        $expectedSku = 'simple';
        $product = $this->productRepository->get($sku);

        self::assertNotEmpty($product);
        self::assertEquals($expectedSku, $product->getSku());
    }

    /**
     * Get list of SKU variations for the same product.
     *
     * @return array
     */
    public function skuDataProvider(): array
    {
        return [
            ['sku' => 'simple'],
            ['sku' => 'Simple'],
            ['sku' => 'simple '],
        ];
    }

    /**
     * Test save product with gallery image
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_image.php
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testSaveProductWithGalleryImage(): void
    {
        /** @var $mediaConfig \Magento\Catalog\Model\Product\Media\Config */
        $mediaConfig = Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Model\Product\Media\Config::class);

        /** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
        $mediaDirectory = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class)
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        $product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $product->load(1);

        $path = $mediaConfig->getBaseMediaPath() . '/magento_image.jpg';
        $absolutePath = $mediaDirectory->getAbsolutePath() . $path;
        $product->addImageToMediaGallery(
            $absolutePath,
            [
            'image',
            'small_image',
            ],
            false,
            false
        );

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productRepository->save($product);

        $gallery = $product->getData('media_gallery');
        $this->assertArrayHasKey('images', $gallery);
        $images = array_values($gallery['images']);

        $this->assertNotEmpty($gallery);
        $this->assertTrue(isset($images[0]['file']));
        $this->assertStringStartsWith('/m/a/magento_image', $images[0]['file']);
        $this->assertArrayHasKey('media_type', $images[0]);
        $this->assertEquals('image', $images[0]['media_type']);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('image'));
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('small_image'));
    }

    /**
     * Test Product Repository can change(update) "sku" for given product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testUpdateProductSku()
    {
        $newSku = 'simple-edited';
        $productId = $this->productResource->getIdBySku('simple');
        $initialProduct = $this->productFactory->create();
        $this->productResource->load($initialProduct, $productId);

        $initialProduct->setSku($newSku);
        $this->productRepository->save($initialProduct);

        $updatedProduct = $this->productFactory->create();
        $this->productResource->load($updatedProduct, $productId);
        self::assertSame($newSku, $updatedProduct->getSku());

        //clean up.
        $this->productRepository->delete($updatedProduct);
    }

    /**
     * Test authorization when saving product's design settings.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     */
    public function testSaveDesign()
    {
        $product = $this->productRepository->get('simple');
        $this->auth->login(TestBootstrap::ADMIN_NAME, TestBootstrap::ADMIN_PASSWORD);

        //Admin doesn't have access to product's design.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_product_design');

        $product->setCustomAttribute('custom_design', 2);
        $product = $this->productRepository->save($product);
        $this->assertEmpty($product->getCustomAttribute('custom_design'));

        //Admin has access to products' design.
        $this->aclBuilder->getAcl()
            ->allow(null, ['Magento_Catalog::products','Magento_Catalog::edit_product_design']);

        $product->setCustomAttribute('custom_design', 2);
        $product = $this->productRepository->save($product);
        $this->assertNotEmpty($product->getCustomAttribute('custom_design'));
        $this->assertEquals(2, $product->getCustomAttribute('custom_design')->getValue());
    }
}
