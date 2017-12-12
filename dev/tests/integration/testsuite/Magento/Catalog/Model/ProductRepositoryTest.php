<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Catalog\Model\ProductRepository
 */
class ProductRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepository
     */
    protected $model;

    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->create(ProductRepository::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_camel_case_sku.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGet()
    {
        $product = $this->model->get('camelcaseproduct');

        $this->assertEquals('CamelCaseProduct', $product->getSku());
    }
}
