<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Exception;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Session;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    /**
     * @var ProductHelper
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->get(ProductHelper::class);
        /** @var ProductInterfaceFactory $productInterfaceFactory */
        $this->productFactory = $this->objectManager->get(ProductInterfaceFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     */
    public function testGetProductUrl()
    {
        $expectedUrl = 'http://localhost/index.php/simple-product.html';
        $product = $this->productRepository->get('simple');
        $this->assertEquals($expectedUrl, $this->helper->getProductUrl($product));

        // product as ID
        $this->assertEquals($expectedUrl, $this->helper->getProductUrl($product->getId()));
    }

    public function testGetPrice()
    {
        /** @var $product Product */
        $product = $this->productFactory->create();
        $product->setPrice(49.95);
        $this->assertEquals(49.95, $this->helper->getPrice($product));
    }

    public function testGetFinalPrice()
    {
        /** @var $product Product */
        $product = $this->productFactory->create();
        $product->setPrice(49.95);
        $product->setFinalPrice(49.95);
        $this->assertEquals(49.95, $this->helper->getFinalPrice($product));
    }

    public function testGetImageUrl()
    {
        /** @var $product Product */
        $product = $this->productFactory->create();
        $this->assertStringEndsWith('placeholder/image.jpg', $this->helper->getImageUrl($product));

        $product->setImage('test_image.png');
        $this->assertStringEndsWith('/test_image.png', $this->helper->getImageUrl($product));
    }

    public function testGetSmallImageUrl()
    {
        /** @var $product Product */
        $product = $this->productFactory->create();
        $this->assertStringEndsWith('placeholder/small_image.jpg', $this->helper->getSmallImageUrl($product));

        $product->setSmallImage('test_image.png');
        $this->assertStringEndsWith('/test_image.png', $this->helper->getSmallImageUrl($product));
    }

    public function testGetThumbnailUrl()
    {
        /** @var $product Product */
        $product = $this->productFactory->create();
        $this->assertStringEndsWith('placeholder/thumbnail.jpg', $this->helper->getThumbnailUrl($product));
        $product->setThumbnail('test_image.png');
        $this->assertStringEndsWith('/test_image.png', $this->helper->getThumbnailUrl($product));
    }

    public function testGetEmailToFriendUrl()
    {
        $product = $this->productFactory->create();
        $product->setId(100);
        $category = $this->objectManager->create(CategoryInterfaceFactory::class)->create();
        $category->setId(10);
        $this->registry->register('current_category', $category);

        try {
            $this->assertStringEndsWith(
                'sendfriend/product/send/id/100/cat_id/10/',
                $this->helper->getEmailToFriendUrl($product)
            );
            $this->registry->unregister('current_category');
        } catch (Exception $e) {
            $this->registry->unregister('current_category');
            throw $e;
        }
    }

    public function testGetStatuses()
    {
        $this->assertEquals([], $this->helper->getStatuses());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testCanShow()
    {
        // non-visible or disabled
        /** @var $product Product */
        $product = $this->productFactory->create();
        $this->assertFalse($this->helper->canShow($product));
        $existingProduct = $this->productRepository->get('simple');

        // enabled and visible
        $product->setId($existingProduct->getId());
        $product->setStatus(Status::STATUS_ENABLED);
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $this->assertTrue($this->helper->canShow($product));

        $this->assertTrue($this->helper->canShow((int)$product->getId()));
    }

    public function testCanUseCanonicalTagDefault()
    {
        $this->assertEquals('0', $this->helper->canUseCanonicalTag());
    }

    /**
     * @magentoConfigFixture current_store catalog/seo/product_canonical_tag 1
     */
    public function testCanUseCanonicalTag()
    {
        $this->assertEquals(1, $this->helper->canUseCanonicalTag());
    }

    public function testGetAttributeInputTypes()
    {
        $types = $this->helper->getAttributeInputTypes();
        $this->assertArrayHasKey('multiselect', $types);
        $this->assertArrayHasKey('boolean', $types);
        foreach ($types as $type) {
            $this->assertIsArray($type);
            $this->assertNotEmpty($type);
        }

        $this->assertNotEmpty($this->helper->getAttributeInputTypes('multiselect'));
        $this->assertNotEmpty($this->helper->getAttributeInputTypes('boolean'));
    }

    public function testGetAttributeBackendModelByInputType()
    {
        $this->assertNotEmpty($this->helper->getAttributeBackendModelByInputType('multiselect'));
        $this->assertNull($this->helper->getAttributeBackendModelByInputType('boolean'));
    }

    public function testGetAttributeSourceModelByInputType()
    {
        $this->assertNotEmpty($this->helper->getAttributeSourceModelByInputType('boolean'));
        $this->assertNull($this->helper->getAttributeSourceModelByInputType('multiselect'));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testInitProduct()
    {
        $this->objectManager->get(Session::class)->setLastVisitedCategoryId(2);
        $product = $this->productRepository->get('simple');
        $this->helper->initProduct($product->getId(), 'view');

        $this->assertInstanceOf(Product::class, $this->registry->registry('current_product'));
        $this->assertInstanceOf(Category::class, $this->registry->registry('current_category'));
    }

    public function testPrepareProductOptions()
    {
        /** @var $product Product */
        $product = $this->productFactory->create();
        $buyRequest = new DataObject(['qty' => 100, 'options' => ['option' => 'value']]);
        $this->helper->prepareProductOptions($product, $buyRequest);
        $result = $product->getPreconfiguredValues();
        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertEquals(100, $result->getQty());
        $this->assertEquals(['option' => 'value'], $result->getOptions());
    }
}
