<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @magentoDbIsolation enabled
     */
    public function testSaveFailure()
    {
        $this->markTestSkipped("When MAGETWO-36510 is fixed, need to change Dbisolation to disabled");
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
