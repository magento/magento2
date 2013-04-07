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
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Block_Product_List_Related.
 *
 * @magentoDataFixture Mage/Catalog/_files/products_related.php
 * @magentoDataFixture Mage/Core/_files/frontend_default_theme.php
 */
class Mage_Catalog_Block_Product_List_RelatedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Block_Product_List_Related
     */
    protected $_block;
    
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    protected function setUp()
    {
        $this->_block = Mage::getObjectManager()->create('Mage_Catalog_Block_Product_List_Related');
        $this->_block->setTemplate('Mage_Catalog::product/list/related.twig');
        $this->_product = Mage::getModel('Mage_Catalog_Model_Product');
        $this->_product->load(1);
        $this->_product->setDoNotUseCategoryId(true);
        $items = array();
        $items[] = array(
            'thumbnailUrl' => "",
            'thumbnailSize' => 50,
            'composite' => $this->_product->isComposite(),
            'saleable' => $this->_product->isSaleable(),
            'hasRequiredOptions' => $this->_product->getRequiredOptions(),
            'id' => $this->_product->getId(),
            'productUrl' => $this->_product->getProductUrl(),
            'name' => $this->_product->getName(),
            'product' => $this->_product
        );
        $this->_block->assign(array("block" => $this->_block, "related" => array("items" => $items)));
        Mage::unregister('product');
        Mage::register('product', $this->_product);
    }
    
    public function testGetRelated()
    {
        $html = $this->_block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertContains('Simple Related Product', $html); /* name */
        $this->assertContains('product/1/', $html);  /* part of url */
    }
}
