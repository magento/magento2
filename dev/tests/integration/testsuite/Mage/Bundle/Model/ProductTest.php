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

/**
 * As far none class is present as separate bundle product,
 * this test is clone of Mage_Catalog_Model_Product with product type "bundle"
 */
class Mage_Bundle_Model_ProductTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product;
        $this->_model->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
    }

    public function testGetTypeId()
    {
        $this->assertEquals(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, $this->_model->getTypeId());
    }

    public function testGetSetTypeInstance()
    {
        // model getter
        $typeInstance = $this->_model->getTypeInstance();
        $this->assertInstanceOf('Mage_Bundle_Model_Product_Type', $typeInstance);
        $this->assertSame($typeInstance, $this->_model->getTypeInstance());

        // singleton getter
        $otherProduct = new Mage_Catalog_Model_Product;
        $otherProduct->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
        $this->assertSame($typeInstance, $otherProduct->getTypeInstance());

        // model setter
        $customTypeInstance = new Mage_Bundle_Model_Product_Type;
        $this->_model->setTypeInstance($customTypeInstance);
        $this->assertSame($customTypeInstance, $this->_model->getTypeInstance());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testCRUD()
    {
        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        $this->_model->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
            ->setAttributeSetId(4)
            ->setName('Bundle Product')->setSku(uniqid())->setPrice(10)
            ->setMetaTitle('meta title')->setMetaKeyword('meta keyword')->setMetaDescription('meta description')
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
        ;
        $crud = new Magento_Test_Entity($this->_model, array('sku' => uniqid()));
        $crud->testCrud();
    }

    public function testGetPriceModel()
    {
        $this->_model->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
        $type = $this->_model->getPriceModel();
        $this->assertInstanceOf('Mage_Bundle_Model_Product_Price', $type);
        $this->assertSame($type, $this->_model->getPriceModel());
    }

    public function testIsComposite()
    {
        $this->assertTrue($this->_model->isComposite());
    }
}
