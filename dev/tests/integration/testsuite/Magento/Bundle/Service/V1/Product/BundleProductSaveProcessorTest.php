<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Service\V1\Product;

use Magento\Bundle\Service\V1\Data\Product\Link;
use Magento\Bundle\Service\V1\Data\Product\LinkBuilder;
use Magento\Bundle\Service\V1\Data\Product\Option;
use Magento\Bundle\Service\V1\Data\Product\OptionBuilder;
use Magento\Catalog\Service\V1\Data\Product;
use Magento\Catalog\Service\V1\Data\ProductBuilder;
use Magento\Catalog\Service\V1\Data\ProductMapper;
use Magento\Catalog\Service\V1\ProductServiceInterface;
use Magento\Catalog\Service\V1\Product\ProductLoader;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for service layer \Magento\Bundle\Service\V1\Product\BundleProductSaveProcessor
 *
 */
class BundleProductSaveProcessorTest extends \PHPUnit_Framework_TestCase
{

    /** @var ProductMapper */
    protected $productMapper;

    /** @var ObjectManager */
    private $objectManager;

    /** @var ProductLoader */
    private $productLoader;

    /** @var ProductServiceInterface */
    private $productService;

    /** @var ProductBuilder */
    private $productBuilder;

    /** @var LinkBuilder */
    private $linkBuilder;

    /** @var OptionBuilder */
    private $optionBuilder;

    /** @var \Magento\Bundle\Model\Product\Type $productType */
    private $productType;

    /** @var  \Magento\Catalog\Model\ProductRepository */
    private $productRepository;


    /**
     * Initialize dependencies
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productType = $this->objectManager->create('Magento\Bundle\Model\Product\Type');
        $this->productRepository = $this->objectManager->get('Magento\Catalog\Model\ProductRepository');
        $this->productLoader = $this->objectManager->create('Magento\Catalog\Service\V1\Product\ProductLoader');
        $this->productMapper = $this->objectManager->create('Magento\Catalog\Service\V1\Data\ProductMapper');
        $this->productService = $this->objectManager->create('Magento\Catalog\Service\V1\ProductServiceInterface');
        $this->productBuilder = $this->objectManager->create('Magento\Catalog\Service\V1\Data\ProductBuilder');
        $this->linkBuilder = $this->objectManager->create('Magento\Bundle\Service\V1\Data\Product\LinkBuilder');
        $this->optionBuilder = $this->objectManager->create('Magento\Bundle\Service\V1\Data\Product\OptionBuilder');

        // create existing options
    }

    /**
     * Create bundle product data for use in creating a new product
     *
     * @return Product
     */
    private function createBundleProductData($skuSuffix)
    {
        /** @var Link $firstLink */
        $firstLink = $this->linkBuilder
            ->setSku('simple')
            ->create();
        /** @var Link[] $links */
        $links = array($firstLink);

        /** @var Option $firstOption */
        $firstOption = $this->optionBuilder
            ->setProductLinks($links)
            ->create();

        /** @var Product bundleProduct */
        $bundleProduct = $this->productBuilder
            ->setSku('sku-z' . $skuSuffix)
            ->setName('Fancy Bundle')
            ->setTypeId(Type::TYPE_BUNDLE)
            ->setPrice(50.00)
            ->setCustomAttribute('bundle_product_options', array($firstOption))
            ->setCustomAttribute('price_view', 'test')
            ->create();

        return $bundleProduct;
    }

    /**
     * Test creation of bundle product through ProductService.
     * data fixture below automatically isolates the db
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     */
    public function testCreateBundleProduct()
    {
        /** @var Product $bundleProduct */
        $bundleProduct = $this->createBundleProductData('-create');
        $sku = $this->productService->create($bundleProduct);
        $this->assertEquals('sku-z-create', $sku);

        // load and confirm number of options and links
        /** @var Product $savedProduct */
        $savedProduct = $this->productService->get($sku);
        /** @var Option[] $updatedOptions */
        $savedOptions = $savedProduct->getCustomAttribute('bundle_product_options')->getValue();
        $this->assertTrue(is_array($savedOptions));
        $this->assertEquals(1, count($savedOptions));
        $option = $savedOptions[0];
        $linkedProducts = $option->getProductLinks();
        $this->assertTrue(is_array($linkedProducts));
        $this->assertEquals(1, count($linkedProducts));
        $link = $linkedProducts[0];
        $this->assertEquals('simple', $link->getSku());
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testUpdateBundleProduct()
    {
        // get existing bundle product
        $savedProduct = $this->productService->get('bundle-product');

        /** @var Link $newLink */
        $newLink = $this->linkBuilder
            ->setSku('simple2')
            ->create();
        /** @var Link[] $links */
        $links = array($newLink);

        /** @var Option $newOption */
        $newOption = $this->optionBuilder
            ->setProductLinks($links)
            ->create();

        /** @var Product bundleProduct */
        $updatedBundleProduct = $this->productBuilder
            ->populate($savedProduct)
            ->setCustomAttribute('bundle_product_options', array($newOption))
            ->setCustomAttribute('price_view', 'test')
            ->setCustomAttribute('price', 10)
            ->create();

        $this->assertEquals('bundle-product', $this->productService->update('bundle-product', $updatedBundleProduct));
        $this->productRepository->get('bundle-product')->unsetData('_cache_instance_options_collection');

        // load and confirm number of links and options
        $savedProduct = $this->productService->get('bundle-product');
        /** @var Option[] $updatedOptions */
        $savedOptions = $savedProduct->getCustomAttribute('bundle_product_options')->getValue();
        $this->assertTrue(is_array($savedOptions));
        $this->assertEquals(1, count($savedOptions));
        $option = $savedOptions[0];
        $linkedProducts = $option->getProductLinks();
        $this->assertTrue(is_array($linkedProducts));
        $this->assertEquals(1, count($linkedProducts));
        $link = $linkedProducts[0];
        $this->assertEquals('simple2', $link->getSku());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteBundleProduct()
    {
        $existingProduct = $this->productService->get('bundle-product');
        $this->assertNotNull($existingProduct);
        $this->assertTrue($this->productService->delete('bundle-product'));
        $this->productService->get('bundle-product');
    }
}
