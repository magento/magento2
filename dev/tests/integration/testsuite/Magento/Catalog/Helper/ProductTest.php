<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_helper;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Helper\Product'
        );
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testGetProductUrl()
    {
        $expectedUrl = 'http://localhost/index.php/simple-product.html';

        // product as object
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        $this->assertEquals($expectedUrl, $this->_helper->getProductUrl($product));

        // product as ID
        $this->assertEquals($expectedUrl, $this->_helper->getProductUrl(1));
    }

    public function testGetPrice()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setPrice(49.95);
        $this->assertEquals(49.95, $this->_helper->getPrice($product));
    }

    public function testGetFinalPrice()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setFinalPrice(49.95);
        $this->assertEquals(49.95, $this->_helper->getFinalPrice($product));
    }

    public function testGetImageUrl()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertStringEndsWith('placeholder/image.jpg', $this->_helper->getImageUrl($product));

        $product->setImage('test_image.png');
        $this->assertStringEndsWith('/test_image.png', $this->_helper->getImageUrl($product));
    }

    public function testGetSmallImageUrl()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertStringEndsWith('placeholder/small_image.jpg', $this->_helper->getSmallImageUrl($product));

        $product->setSmallImage('test_image.png');
        $this->assertStringEndsWith('/test_image.png', $this->_helper->getSmallImageUrl($product));
    }

    public function testGetThumbnailUrl()
    {
        $this->assertEmpty(
            $this->_helper->getThumbnailUrl(
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
                $this->_helper->getEmailToFriendUrl($product)
            );
            $objectManager->get('Magento\Framework\Registry')->unregister('current_category');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Framework\Registry')->unregister('current_category');
            throw $e;
        }
    }

    public function testGetStatuses()
    {
        $this->assertEquals([], $this->_helper->getStatuses());
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
        $this->assertFalse($this->_helper->canShow($product));

        // enabled and visible
        $product->setId(1);
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $this->assertTrue($this->_helper->canShow($product));

        $this->assertTrue($this->_helper->canShow(1));
    }

    public function testCanUseCanonicalTagDefault()
    {
        $this->assertEquals('0', $this->_helper->canUseCanonicalTag());
    }

    /**
     * @magentoConfigFixture current_store catalog/seo/product_canonical_tag 1
     */
    public function testCanUseCanonicalTag()
    {
        $this->assertEquals(1, $this->_helper->canUseCanonicalTag());
    }

    public function testGetAttributeInputTypes()
    {
        $types = $this->_helper->getAttributeInputTypes();
        $this->assertArrayHasKey('multiselect', $types);
        $this->assertArrayHasKey('boolean', $types);
        foreach ($types as $type) {
            $this->assertInternalType('array', $type);
            $this->assertNotEmpty($type);
        }

        $this->assertNotEmpty($this->_helper->getAttributeInputTypes('multiselect'));
        $this->assertNotEmpty($this->_helper->getAttributeInputTypes('boolean'));
    }

    public function testGetAttributeBackendModelByInputType()
    {
        $this->assertNotEmpty($this->_helper->getAttributeBackendModelByInputType('multiselect'));
        $this->assertNull($this->_helper->getAttributeBackendModelByInputType('boolean'));
    }

    public function testGetAttributeSourceModelByInputType()
    {
        $this->assertNotEmpty($this->_helper->getAttributeSourceModelByInputType('boolean'));
        $this->assertNull($this->_helper->getAttributeSourceModelByInputType('multiselect'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     */
    public function testInitProduct()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Session'
        )->setLastVisitedCategoryId(
            2
        );
        $this->_helper->initProduct(1, 'view');
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

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
        $this->_helper->prepareProductOptions($product, $buyRequest);
        $result = $product->getPreconfiguredValues();
        $this->assertInstanceOf('Magento\Framework\DataObject', $result);
        $this->assertEquals(100, $result->getQty());
        $this->assertEquals(['option' => 'value'], $result->getOptions());
    }
}
