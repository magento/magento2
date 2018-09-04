<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->model = $this->objectManager->get(Product::class);
    }

    /**
     * Checks a possibility to retrieve product raw attribute value.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetAttributeRawValue()
    {
        $sku = 'simple';
        $attribute = 'name';

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku);

        $actual = $this->model->getAttributeRawValue($product->getId(), $attribute, null);
        self::assertEquals($product->getName(), $actual);
    }
}
