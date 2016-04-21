<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Helper\Product'
        );

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $this->productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Api\ProductRepositoryInterface');
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoAppIsolation enabled
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
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setPrice(49.95);
        $this->assertEquals(49.95, $this->helper->getPrice($product));
    }

    public function testGetFinalPrice()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setFinalPrice(49.95);
        $this->assertEquals(49.95, $this->helper->getFinalPrice($product));
    }

    public function testGetImageUrl()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertStringEndsWith('placeholder/image.jpg', $this->helper->getImageUrl($product));

        $product->setImage('test_image.png');
        $this->assertStringEndsWith('/test_image.png', $this->helper->getImageUrl($product));
    }

    public function testGetSmallImageUrl()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertStringEndsWith('placeholder/small_image.jpg', $this->helper->getSmallImageUrl($product));

        $product->setSmallImage('test_image.png');
        $this->assertStringEndsWith('/test_image.png', $this->helper->getSmallImageUrl($product));
    }

    public function testGetThumbnailUrl()
    {
        $this->assertEmpty(
            $this->helper->getThumbnailUrl(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product')
            )
        );
    }

    public function testGetEmailToFriendUrl()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setId(100);
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        $category->setId(10);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_category', $category);

        try {
            $this->assertStringEndsWith(
                'sendfriend/product/send/id/100/cat_id/10/',
                $this->helper->getEmailToFriendUrl($product)
            );
            $objectManager->get('Magento\Framework\Registry')->unregister('current_category');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Framework\Registry')->unregister('current_category');
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
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertFalse($this->helper->canShow($product));
        $existingProduct = $this->productRepository->get('simple');

        // enabled and visible
        $product->setId($existingProduct->getId());
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
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
            $this->assertInternalType('array', $type);
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
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testInitProduct()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $objectManager->get('Magento\Catalog\Model\Session')->setLastVisitedCategoryId(2);
        $product = $this->productRepository->get('simple');
        $this->helper->initProduct($product->getId(), 'view');

        $this->assertInstanceOf(
            'Magento\Catalog\Model\Product',
            $objectManager->get('Magento\Framework\Registry')->registry('current_product')
        );
        $this->assertInstanceOf(
            'Magento\Catalog\Model\Category',
            $objectManager->get('Magento\Framework\Registry')->registry('current_category')
        );
    }

    public function testPrepareProductOptions()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $buyRequest = new \Magento\Framework\DataObject(['qty' => 100, 'options' => ['option' => 'value']]);
        $this->helper->prepareProductOptions($product, $buyRequest);
        $result = $product->getPreconfiguredValues();
        $this->assertInstanceOf('Magento\Framework\DataObject', $result);
        $this->assertEquals(100, $result->getQty());
        $this->assertEquals(['option' => 'value'], $result->getOptions());
    }
}
