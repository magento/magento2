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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Helper_MapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Helper_Map
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = new Mage_Catalog_Helper_Map;
    }

    public function testGetCategoryUrl()
    {
        $this->assertStringEndsWith('/catalog/seo_sitemap/category/', $this->_helper->getCategoryUrl());
    }

    public function testGetProductUrl()
    {
        $this->assertStringEndsWith('/catalog/seo_sitemap/product/', $this->_helper->getProductUrl());
    }

    public function testGetIsUseCategoryTreeModeDefault()
    {
        $this->assertFalse($this->_helper->getIsUseCategoryTreeMode());
    }

    /**
     * @magentoConfigFixture current_store catalog/sitemap/tree_mode 1
     */
    public function testGetIsUseCategoryTreeMode()
    {
        $this->assertTrue($this->_helper->getIsUseCategoryTreeMode());
    }
}
