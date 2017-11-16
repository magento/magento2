<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Catalog\Controller\Product.
 */
namespace Magento\Catalog\Controller;

/**
 * @magentoAppIsolation enabled
 */
class ProductTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Randomly fails due to known HHVM bug (DOMText mixed with DOMElement)');
        }
        parent::setUp();
    }

    public function assert404NotFound()
    {
        parent::assert404NotFound();
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->assertNull($objectManager->get(\Magento\Framework\Registry::class)->registry('current_product'));
    }

    protected function _getProductImageFile()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /**
         * @var $repository \Magento\Catalog\Model\ProductRepository
         */
        $repository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $product = $repository->get('simple_product_1');
        $images = $product->getMediaGalleryImages()->getItems();
        $image = reset($images);
        return $image['file'];
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     * @magentoAppArea frontend
     */
    public function testViewAction()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /**
         * @var $repository \Magento\Catalog\Model\ProductRepository
         */
        $repository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $product = $repository->get('simple_product_1');
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getEntityId()));

        /** @var $currentProduct \Magento\Catalog\Model\Product */
        $currentProduct = $objectManager->get(\Magento\Framework\Registry::class)->registry('current_product');
        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $currentProduct);
        $this->assertEquals($product->getEntityId(), $currentProduct->getEntityId());

        $lastViewedProductId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Session::class
        )->getLastViewedProductId();
        $this->assertEquals($product->getEntityId(), $lastViewedProductId);

        $responseBody = $this->getResponse()->getBody();
        /* Product info */
        $this->assertContains('Simple Product 1 Name', $responseBody);
        $this->assertContains('Simple Product 1 Full Description', $responseBody);
        $this->assertContains('Simple Product 1 Short Description', $responseBody);
        /* Stock info */
        $this->assertContains('$1,234.56', $responseBody);
        $this->assertContains('In stock', $responseBody);
        $this->assertContains('Add to Cart', $responseBody);
        /* Meta info */
        $this->assertContains('<title>Simple Product 1 Meta Title</title>', $responseBody);
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//meta[@name="keywords" and @content="Simple Product 1 Meta Keyword"]',
                $responseBody
            )
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//meta[@name="description" and @content="Simple Product 1 Meta Description"]',
                $responseBody
            )
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testViewActionConfigurable()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /**
         * @var $repository \Magento\Catalog\Model\ProductRepository
         */
        $repository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $product = $repository->get('simple');
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getEntityId()));
        $html = $this->getResponse()->getBody();
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="product-options-wrapper"]',
                $html
            )
        );
    }

    public function testViewActionNoProductId()
    {
        $this->dispatch('catalog/product/view/id/');
        $this->assert404NotFound();
    }

    public function testViewActionRedirect()
    {
        $this->dispatch('catalog/product/view/?store=default');

        $this->assertRedirect();
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     */
    public function testGalleryAction()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /**
         * @var $repository \Magento\Catalog\Model\ProductRepository
         */
        $repository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $product = $repository->get('simple_product_1');
        $this->dispatch(sprintf('catalog/product/gallery/id/%s', $product->getEntityId()));

        $this->assertContains('http://localhost/pub/media/catalog/product/', $this->getResponse()->getBody());
        $this->assertContains($this->_getProductImageFile(), $this->getResponse()->getBody());
    }

    public function testGalleryActionRedirect()
    {
        $this->dispatch('catalog/product/gallery/?store=default');

        $this->assertRedirect();
    }

    public function testGalleryActionNoProduct()
    {
        $this->dispatch('catalog/product/gallery/id/');

        $this->assert404NotFound();
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     */
    public function testImageAction()
    {
        $this->markTestSkipped("All logic has been cut to avoid possible malicious usage of the method");
        ob_start();
        /* Preceding slash in URL is required in this case */
        $this->dispatch('/catalog/product/image' . $this->_getProductImageFile());
        $imageContent = ob_get_clean();
        /**
         * Check against PNG file signature.
         * @link http://www.libpng.org/pub/png/spec/1.2/PNG-Rationale.html#R.PNG-file-signature
         */
        $this->assertStringStartsWith(sprintf("%cPNG\r\n%c\n", 137, 26), $imageContent);
    }

    public function testImageActionNoImage()
    {
        $this->dispatch('catalog/product/image/');

        $this->assert404NotFound();
    }
}
