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
namespace Magento\Catalog\Model\Product\Attribute\Backend;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Sku.
 * @magentoAppArea adminhtml
 */
class SkuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGenerateUniqueSkuExistingProduct()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        $product->setId(null);
        $this->assertEquals('simple', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('simple-1', $product->getSku());
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @dataProvider uniqueSkuDataProvider
     */
    public function testGenerateUniqueSkuNotExistingProduct($product)
    {
        $this->assertEquals('simple', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('simple', $product->getSku());
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @dataProvider uniqueLongSkuDataProvider
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testGenerateUniqueLongSku($product)
    {
        /** @var \Magento\Catalog\Model\Product\Copier $copier */
        $copier = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product\Copier'
        );
        $copier->copy($product);
        $this->assertEquals('0123456789012345678901234567890123456789012345678901234567890123', $product->getSku());
        $product->getResource()->getAttribute('sku')->getBackend()->beforeSave($product);
        $this->assertEquals('01234567890123456789012345678901234567890123456789012345678901-1', $product->getSku());
    }

    /**
     * Returns simple product
     *
     * @return array
     */
    public function uniqueSkuDataProvider()
    {
        $product = $this->_getProduct();
        return array(array($product));
    }

    /**
     * Returns simple product
     *
     * @return array
     */
    public function uniqueLongSkuDataProvider()
    {
        $product = $this->_getProduct();
        $product->setSku('0123456789012345678901234567890123456789012345678901234567890123');
        //strlen === 64
        return array(array($product));
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
            'Magento\Catalog\Model\Product'
        );
        $product->setTypeId(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        )->setId(
            1
        )->setAttributeSetId(
            4
        )->setWebsiteIds(
            array(1)
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
            array(2)
        )->setStockData(
            array('use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1)
        );
        return $product;
    }
}
