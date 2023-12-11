<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller;

use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Catalog\Model\Session;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Framework\Registry;
use Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Checks product view on storefront
 *
 * @see \Magento\Catalog\Controller\Product
 *
 * @magentoDbIsolation enabled
 */
class ProductTest extends AbstractController
{
    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Session */
    private $session;

    /**
     * @var DataFixtureStorage
     */
    private $fixture;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Randomly fails due to known HHVM bug (DOMText mixed with DOMElement)');
        }
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                \Magento\Catalog\Model\Product\Attribute\LayoutUpdateManager::class =>
                    \Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager::class
            ]
        ]);
        parent::setUp();

        $this->registry = $this->_objectManager->get(Registry::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->session = $this->_objectManager->get(Session::class);
        $this->fixture = DataFixtureStorageManager::getStorage();
    }

    /**
     * @inheritdoc
     */
    public function assert404NotFound()
    {
        parent::assert404NotFound();

        $this->assertNull($this->registry->registry('current_product'));
    }

    /**
     * Get product image file
     *
     * @return string
     */
    protected function getProductImageFile(): string
    {
        $product = $this->productRepository->get('simple_product_1');
        $images = $product->getMediaGalleryImages()->getItems();
        $image = reset($images);

        return $image['file'];
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testViewAction(): void
    {
        $product = $this->productRepository->get('simple_product_1');
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getEntityId()));
        $currentProduct = $this->registry->registry('current_product');

        $this->assertInstanceOf(ProductInterface::class, $currentProduct);
        $this->assertEquals($product->getEntityId(), $currentProduct->getEntityId());
        $this->assertEquals($product->getEntityId(), $this->session->getLastViewedProductId());

        $responseBody = $this->getResponse()->getBody();
        /* Product info */
        $this->assertStringContainsString($product->getName(), $responseBody);
        $this->assertStringContainsString($product->getDescription(), $responseBody);
        $this->assertStringContainsString($product->getShortDescription(), $responseBody);
        $this->assertStringContainsString($product->getSku(), $responseBody);
        /* Stock info */
        $this->assertStringContainsString('$1,234.56', $responseBody);
        $this->assertStringContainsString('In stock', $responseBody);
        $this->assertStringContainsString((string)__('Add to Cart'), $responseBody);
        /* Meta info */
        $this->assertStringContainsString('<title>Simple Product 1 Meta Title</title>', $responseBody);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//meta[@name="keywords" and @content="Simple Product 1 Meta Keyword"]',
                $responseBody
            )
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//meta[@name="description" and @content="Simple Product 1 Meta Description"]',
                $responseBody
            )
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testViewActionConfigurable(): void
    {
        $product = $this->productRepository->get('simple');
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getEntityId()));
        $html = $this->getResponse()->getBody();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//*[@id="product-options-wrapper"]',
                $html
            )
        );
    }

    /**
     * @return void
     */
    public function testViewActionNoProductId(): void
    {
        $this->dispatch('catalog/product/view/id/');

        $this->assert404NotFound();
    }

    /**
     * @return void
     */
    public function testViewActionRedirect(): void
    {
        $this->dispatch('catalog/product/view/?store=default');

        $this->assertRedirect();
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     * @return void
     */
    public function testGalleryAction(): void
    {
        $product = $this->productRepository->get('simple_product_1');
        $this->dispatch(sprintf('catalog/product/gallery/id/%s', $product->getEntityId()));

        $this->assertStringContainsString(
            'http://localhost/media/catalog/product/',
            $this->getResponse()->getBody()
        );
        $this->assertStringContainsString($this->getProductImageFile(), $this->getResponse()->getBody());
    }

    /**
     * @return void
     */
    public function testGalleryActionRedirect(): void
    {
        $this->dispatch('catalog/product/gallery/?store=default');

        $this->assertRedirect();
    }

    /**
     * @return void
     */
    public function testGalleryActionNoProduct(): void
    {
        $this->dispatch('catalog/product/gallery/id/');

        $this->assert404NotFound();
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     * @return void
     */
    public function testImageAction(): void
    {
        $this->markTestSkipped("All logic has been cut to avoid possible malicious usage of the method");
        ob_start();
        /* Preceding slash in URL is required in this case */
        $this->dispatch('/catalog/product/image' . $this->getProductImageFile());
        $imageContent = ob_get_clean();
        /**
         * Check against PNG file signature.
         *
         * @link http://www.libpng.org/pub/png/spec/1.2/PNG-Rationale.html#R.PNG-file-signature
         */
        $this->assertStringStartsWith(sprintf("%cPNG\r\n%c\n", 137, 26), $imageContent);
    }

    /**
     * @return void
     */
    public function testImageActionNoImage(): void
    {
        $this->dispatch('catalog/product/image/');

        $this->assert404NotFound();
    }

    /**
     * Check that custom layout update files is employed.
     *
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     * @return void
     */
    public function testViewWithCustomUpdate(): void
    {
        //Setting a fake file for the product.
        $file = 'test-file';
        /** @var ProductRepositoryInterface $repository */
        $repository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $sku = 'simple_product_1';
        $product = $repository->get($sku);
        $productId = $product->getId();
        /** @var ProductLayoutUpdateManager $layoutManager */
        $layoutManager = Bootstrap::getObjectManager()->get(ProductLayoutUpdateManager::class);
        $layoutManager->setFakeFiles((int)$productId, [$file]);
        //Updating the custom attribute.
        $product->setCustomAttribute('custom_layout_update_file', $file);
        $repository->save($product);

        //Viewing the product
        $this->dispatch("catalog/product/view/id/$productId");
        //Layout handles must contain the file.
        $handles = Bootstrap::getObjectManager()->get(\Magento\Framework\View\LayoutInterface::class)
            ->getUpdate()
            ->getHandles();
        $this->assertContains("catalog_product_view_selectable_{$sku}_{$file}", $handles);
    }

    /**
     * Validate itemprop generation on different product types' page
     *
     * @return void
     */
    #[
        DataFixture(Category::class, as: 'category'),
        DataFixture(
            Product::class,
            ['category_ids' => ['$category.id$'], 'short_description' => 'Product Short Description'],
            as: 'sp'
        ),
        DataFixture(AttributeFixture::class, ['options' => [['label' => 'option1', 'sort_order' => 0]]], as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'short_description' => 'Configurable Product Short Description',
                '_options' => ['$attr$'],
                '_links' => ['$sp$']
            ],
            as: 'cp'
        ),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$sp.sku$'], 'link1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$']], 'opt1'),
        DataFixture(
            BundleProductFixture::class,
            [
                'short_description' => 'Bundle Product Short Description',
                'sku' => 'bundle1',
                '_options' => ['$opt1$']
            ],
            as:'bp'
        ),
    ]
    public function testItempropOnProductPage()
    {
        $product = $this->fixture->get('sp');
        $this->checkItemProp($product);
        $bundleProduct = $this->fixture->get('bp');
        $this->checkItemProp($bundleProduct);
        $configurableProduct = $this->fixture->get('cp');
        $this->checkItemProp($configurableProduct);
    }

    /**
     * Validate presence of itemprop for image and description
     *
     * @param ProductInterface $product
     * @return void
     */
    private function checkItemProp(ProductInterface $product)
    {
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));
        $html = $this->getResponse()->getBody();
        $this->assertNotEmpty(
            Xpath::getElementsCountForXpath(
                '//*[@itemprop="image"]',
                $html
            ),
            'itemprop image doesn\'t match for product type '.$product->getTypeId()
        );
        $this->assertNotEmpty(
            Xpath::getElementsCountForXpath(
                '//*[@itemprop="description"]',
                $html
            ),
            'itemprop description doesn\'t match for product type '.$product->getTypeId()
        );
    }
}
