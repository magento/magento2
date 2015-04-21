<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Plugin;

class BundleSaveOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
    }

    public static function tearDownAfterClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\CatalogInventory\Model\StockRegistry $stockRegistry */
        $stockRegistry = $objectManager->get('Magento\CatalogInventory\Model\StockRegistry');
        /** @var \Magento\CatalogInventory\Model\Stock\StockStatusRepository $stockStatusRepository */
        $stockStatusRepository = $objectManager->get('Magento\CatalogInventory\Model\Stock\StockStatusRepository');
        $isSecureArea = $objectManager->get('Magento\Framework\Registry')->registry('isSecureArea');
        $objectManager->get('Magento\Framework\Registry')->unregister('isSecureArea');
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');
        foreach ([3, 2, 1] as $productId) {
            $stockStatus = $stockRegistry->getStockStatus($productId, 1);
            $stockStatusRepository->delete($stockStatus);
        }
        $objectManager->get('Magento\Framework\Registry')->unregister('isSecureArea');
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', $isSecureArea);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation enabled
     */
    public function testSaveSuccess()
    {
        $title = "new title";
        $bundleProductSku = 'bundle-product';
        $product = $this->productRepository->get($bundleProductSku);
        $bundleExtensionAttributes = $product->getExtensionAttributes()->getBundleProductOptions();
        $bundleOption = $bundleExtensionAttributes[0];
        $this->assertEquals(true, $bundleOption->getRequired());
        $bundleOption->setTitle($title);

        $oldDescription = $product->getDescription();
        $description = $oldDescription . "hello";
        $product->setDescription($description);
        $product->getExtensionAttributes()->setBundleProductOptions([$bundleOption]);
        $product = $this->productRepository->save($product);

        $this->assertEquals($description, $product->getDescription());
        $this->assertEquals($title, $product->getExtensionAttributes()->getBundleProductOptions()[0]->getTitle());
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation disabled
     */
    public function testSaveFailure()
    {
        $bundleProductSku = 'bundle-product';
        $product = $this->productRepository->get($bundleProductSku);
        $bundleExtensionAttributes = $product->getExtensionAttributes()->getBundleProductOptions();
        $bundleOption = $bundleExtensionAttributes[0];
        $this->assertEquals(true, $bundleOption->getRequired());
        $bundleOption->setRequired(false);
        //set an incorrect option id to trigger exception
        $bundleOption->setOptionId(-1);

        $description = "hello";

        $product->setDescription($description);
        $product->getExtensionAttributes()->setBundleProductOptions([$bundleOption]);
        $caughtException = false;
        try {
            $this->productRepository->save($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $caughtException = true;
        }

        $this->assertTrue($caughtException);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product')->load($product->getId());
        $this->assertEquals(null, $product->getDescription());
    }
}
