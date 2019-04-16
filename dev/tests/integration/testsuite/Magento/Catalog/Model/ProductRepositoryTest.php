<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Backend\Model\Auth;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Bootstrap as TestBootstrap;
use Magento\Framework\Acl\Builder;

/**
 * Provide tests for ProductRepository model.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test subject.
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->auth = Bootstrap::getObjectManager()->get(Auth::class);
        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
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
            ->allow(null, ['Magento_Catalog::products', 'Magento_Catalog::edit_product_design']);

        $product->setCustomAttribute('custom_design', 2);
        $product = $this->productRepository->save($product);
        $this->assertNotEmpty($product->getCustomAttribute('custom_design'));
        $this->assertEquals(2, $product->getCustomAttribute('custom_design')->getValue());
    }
}
