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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Helper\Data');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetBreadcrumbPath()
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Category');
        $category->load(5);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Registry')->register('current_category', $category);

        try {
            $path = $this->_helper->getBreadcrumbPath();
            $this->assertInternalType('array', $path);
            $this->assertEquals(array('category3', 'category4', 'category5'), array_keys($path));
            $this->assertArrayHasKey('label', $path['category3']);
            $this->assertArrayHasKey('link', $path['category3']);
            $objectManager->get('Magento\Registry')->unregister('current_category');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Registry')->unregister('current_category');
            throw $e;
        }
    }

    public function testGetCategory()
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Category');
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Registry')->register('current_category', $category);
        try {
            $this->assertSame($category, $this->_helper->getCategory());
            $objectManager->get('Magento\Registry')->unregister('current_category');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Registry')->unregister('current_category');
            throw $e;
        }
    }

    public function testGetProduct()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Registry')->register('current_product', $product);
        try {
            $this->assertSame($product, $this->_helper->getProduct());
            $objectManager->get('Magento\Registry')->unregister('current_product');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Registry')->unregister('current_product');
            throw $e;
        }
    }

    public function testSplitSku()
    {
        $sku = 'one-two-three';
        $this->assertEquals(array('on', 'e-', 'tw', 'o-', 'th', 're', 'e'), $this->_helper->splitSku($sku, 2));
    }

    public function testGetAttributeHiddenFields()
    {
        $this->assertEquals(array(), $this->_helper->getAttributeHiddenFields());
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Registry')->register('attribute_type_hidden_fields', 'test');
        try {
            $this->assertEquals('test', $this->_helper->getAttributeHiddenFields());
            $objectManager->get('Magento\Registry')->unregister('attribute_type_hidden_fields');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Registry')->unregister('attribute_type_hidden_fields');
            throw $e;
        }
    }

    public function testGetPriceScopeDefault()
    {
        // $this->assertEquals(\Magento\Core\Model\Store::PRICE_SCOPE_GLOBAL, $this->_helper->getPriceScope());
        $this->assertNull($this->_helper->getPriceScope());
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testGetPriceScope()
    {
        $this->assertEquals(\Magento\Core\Model\Store::PRICE_SCOPE_WEBSITE, $this->_helper->getPriceScope());
    }

    public function testIsPriceGlobalDefault()
    {
        $this->assertTrue($this->_helper->isPriceGlobal());
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testIsPriceGlobal()
    {
        $this->assertFalse($this->_helper->isPriceGlobal());
    }

    public function testShouldSaveUrlRewritesHistoryDefault()
    {
        $this->assertTrue($this->_helper->shouldSaveUrlRewritesHistory());
    }

    /**
     * @magentoConfigFixture current_store catalog/seo/save_rewrites_history 0
     */
    public function testShouldSaveUrlRewritesHistory()
    {
        $this->assertFalse($this->_helper->shouldSaveUrlRewritesHistory());
    }

    public function testIsUsingStaticUrlsAllowedDefault()
    {
        $this->assertFalse($this->_helper->isUsingStaticUrlsAllowed());
    }

    /**
     * isUsingStaticUrlsAllowed()
     * setStoreId()
     * @magentoConfigFixture current_store cms/wysiwyg/use_static_urls_in_catalog 1
     */
    public function testIsUsingStaticUrlsAllowed()
    {
        $this->assertTrue($this->_helper->isUsingStaticUrlsAllowed());
        $this->_helper->setStoreId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
                ->getStore()->getId()
        );
        $this->assertTrue($this->_helper->isUsingStaticUrlsAllowed());
    }

    public function testIsUrlDirectivesParsingAllowedDefault()
    {
        $this->assertTrue($this->_helper->isUrlDirectivesParsingAllowed());
    }

    /**
     * isUrlDirectivesParsingAllowed()
     * setStoreId()
     * @magentoConfigFixture current_store catalog/frontend/parse_url_directives 0
     */
    public function testIsUrlDirectivesParsingAllowed()
    {
        $this->assertFalse($this->_helper->isUrlDirectivesParsingAllowed());
        $this->_helper->setStoreId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
                ->getStore()->getId()
        );
        $this->assertFalse($this->_helper->isUrlDirectivesParsingAllowed());
    }

    public function testGetPageTemplateProcessor()
    {
        $this->assertInstanceOf('Magento\Filter\Template', $this->_helper->getPageTemplateProcessor());
    }
}
