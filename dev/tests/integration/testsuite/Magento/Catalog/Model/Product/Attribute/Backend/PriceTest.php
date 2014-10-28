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
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Price.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product\Attribute\Backend\Price'
        );
        $this->_model->setAttribute(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Eav\Model\Config'
            )->getAttribute(
                'catalog_product',
                'price'
            )
        );
    }

    public function testSetScopeDefault()
    {
        /* validate result of setAttribute */
        $this->assertEquals(
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL,
            $this->_model->getAttribute()->getIsGlobal()
        );
        $this->_model->setScope($this->_model->getAttribute());
        $this->assertEquals(
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL,
            $this->_model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testSetScope()
    {
        $this->_model->setScope($this->_model->getAttribute());
        $this->assertEquals(
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE,
            $this->_model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoConfigFixture current_store currency/options/base GBP
     */
    public function testAfterSave()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        $product->setOrigData();
        $product->setPrice(9.99);
        $product->setStoreId(0);
        $product->save();
        $this->assertEquals(
            '9.99',
            $product->getResource()->getAttributeRawValue(
                $product->getId(),
                $this->_model->getAttribute()->getId(),
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Framework\StoreManagerInterface'
                )->getStore()->getId()
            )
        );
    }
}
