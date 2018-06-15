<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Sku.
 *
 * @magentoAppArea adminhtml
 */
class SkuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGenerateUniqueSkuDuplicatedProduct()
    {
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
        /** @var \Magento\Catalog\Model\Product\Copier $copier */
        $copier = $this->getCopier();
        /** @var \Magento\Catalog\Model\Product; $product2 */
        $product2 = $copier->copy($product);

        $this->assertEquals('simple', $product->getSku());
        $product2->getResource()->getAttribute('sku')->getBackend()->beforeSave($product2);
        $this->assertEquals('simple-1', $product2->getSku());
    }

    /**
     * Checks if generation of unique sku is not allowed
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testPreventGenerateUniqueSkuExistingProduct()
    {
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
        $product->setId(null);
        $this->assertEquals('simple', $product->getSku());

        $this->expectException('Magento\Framework\Exception\AlreadyExistsException');
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);

        $this->fail('Unique sku generation should be allowed only for product duplication.');
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     *
     * @dataProvider uniqueSkuDataProvider
     */
    public function testGenerateUniqueSkuNotExistingProduct($product)
    {
        $this->assertEquals('simple', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('simple', $product->getSku());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testGenerateUniqueLongSkuDuplicatedProduct()
    {
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
        $product->setSku('0123456789012345678901234567890123456789012345678901234567890124');
        $product->save();
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('0123456789012345678901234567890123456789012345678901234567890124', $product->getSku());

        /** @var \Magento\Catalog\Model\Product\Copier $copier */
        $copier = $this->getCopier();
        $product2 = $copier->copy($product);

        $this->assertEquals('01234567890123456789012345678901234567890123456789012345678901-1', $product2->getSku());
        $product2->getResource()->getAttribute('sku')->getBackend()->beforeSave($product2);
        $this->assertEquals('01234567890123456789012345678901234567890123456789012345678901-1', $product2->getSku());

        $product2->setId(null);
        $product2->getResource()->getAttribute('sku')->getBackend()->beforeSave($product2);
        $this->assertEquals('01234567890123456789012345678901234567890123456789012345678901-2', $product2->getSku());
    }

    /**
     * Checks if generation of long unique sku is not allowed
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testPreventGenerateUniqueLongSku()
    {
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
        $product->setSku('0123456789012345678901234567890123456789012345678901234567890123');

        /** @var \Magento\Catalog\Model\Product\Copier $copier */
        $copier = $this->getCopier();

        $copier->copy($product);
        $this->assertEquals('0123456789012345678901234567890123456789012345678901234567890123', $product->getSku());
        $this->expectException('Magento\Framework\Exception\AlreadyExistsException');
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);

        $this->fail('Unique long sku generation should be allowed only for product duplication.');
    }

    /**
     * Returns simple product
     *
     * @return array
     */
    public function uniqueSkuDataProvider()
    {
        $product = $this->_getProduct();

        return [[$product]];
    }

    /**
     * Get product form data provider
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _getProduct()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->setTypeId(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        )->setId(
            1
        )->setAttributeSetId(
            4
        )->setWebsiteIds(
            [1]
        )->setName(
            'Simple Product'
        )->setSku(
            'simple'
        )->setPrice(
            10
        )->setDescription(
            'Description with <b>html tag</b>'
        )->setVisibility(
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
        )->setStatus(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        )->setCategoryIds(
            [2]
        )->setStockData(
            ['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]
        );

        return $product;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Copier
     */
    protected function getCopier()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Product\Copier::class
        );
    }
}
