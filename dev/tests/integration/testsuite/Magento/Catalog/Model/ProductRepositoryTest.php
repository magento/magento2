<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Backend\Model\Auth;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\RulesFactory;

/**
 * Provide tests for ProductRepository model.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 *
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
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * Sets up common objects
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->rulesFactory = Bootstrap::getObjectManager()->get(RulesFactory::class);
        $this->roleFactory = Bootstrap::getObjectManager()->get(RoleFactory::class);
        $this->auth = Bootstrap::getObjectManager()->get(Auth::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->auth->logout();
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
        $product->addImageToMediaGallery($absolutePath, [
            'image',
            'small_image',
        ], false, false);

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
     * Test authorization when saving product's design settings.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/User/_files/user_with_new_role.php
     * @magentoAppArea adminhtml
     */
    public function testSaveDesign()
    {
        $product = $this->productRepository->get('simple');
        /** @var Role $role */
        $role = $this->roleFactory->create();
        $role->load('new_role', 'role_name');
        $this->auth->login('admin_with_role', '12345abc');

        //Admin doesn't have access to product's design.
        /** @var Rules $rules */
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Catalog::products']);
        $rules->saveRel();

        $product->setCustomAttribute('custom_design', 2);
        $product = $this->productRepository->save($product);
        $this->assertEmpty($product->getCustomAttribute('custom_design'));

        //Admin has access to products' design.
        /** @var Rules $rules */
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Catalog::products', 'Magento_Catalog::edit_product_design']);
        $rules->saveRel();

        $product->setCustomAttribute('custom_design', 2);
        $product = $this->productRepository->save($product);
        $this->assertNotEmpty($product->getCustomAttribute('custom_design'));
        $this->assertEquals(2, $product->getCustomAttribute('custom_design')->getValue());
    }
}
