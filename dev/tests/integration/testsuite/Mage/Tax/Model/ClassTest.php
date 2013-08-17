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
 * @package     Magento_Tax
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Tax_Model_ClassTest extends PHPUnit_Framework_TestCase
{
    public function testCheckClassCanBeDeletedCustomerClassAssertException()
    {
        /** @var $model Mage_Tax_Model_Class */
        $model = Mage::getModel('Mage_Tax_Model_Class')->getCollection()
            ->setClassTypeFilter(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)
            ->getFirstItem();

        $this->setExpectedException('Mage_Core_Exception');
        $model->checkClassCanBeDeleted();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCheckClassCanBeDeletedProductClassAssertException()
    {
        /** @var $model Mage_Tax_Model_Class */
        $model = Mage::getModel('Mage_Tax_Model_Class')->getCollection()
            ->setClassTypeFilter(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)
            ->getFirstItem();

        Mage::getModel('Mage_Catalog_Model_Product')
            ->setTypeId('simple')->setAttributeSetId(4)
            ->setName('Simple Product')->setSku(uniqid())->setPrice(10)
            ->setMetaTitle('meta title')->setMetaKeyword('meta keyword')->setMetaDescription('meta description')
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->setTaxClassId($model->getId())
            ->save();

        $this->setExpectedException('Mage_Core_Exception');
        $model->checkClassCanBeDeleted();
    }

    /**
     * @dataProvider classesDataProvider
     */
    public function testCheckClassCanBeDeletedPositiveResult($classType)
    {
        /** @var $model Mage_Tax_Model_Class */
        $model = Mage::getModel('Mage_Tax_Model_Class');
        $model->setClassName('TaxClass' . uniqid())
            ->setClassType($classType)
            ->isObjectNew(true);
        $model->save();

        $this->assertTrue($model->checkClassCanBeDeleted());
    }

    public function classesDataProvider()
    {
        return array(
            array(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER),
            array(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Tax/_files/tax_classes.php
     */
    public function testCheckClassCanBeDeletedCustomerClassUsedInTaxRule()
    {
        /** @var $registry Mage_Core_Model_Registry */
        $registry = Mage::getObjectManager()->get('Mage_Core_Model_Registry');
        /** @var $taxRule Mage_Tax_Model_Calculation_Rule */
        $taxRule = $registry->registry('_fixture/Mage_Tax_Model_Calculation_Rule');
        $customerClasses = $taxRule->getCustomerTaxClasses();

        /** @var $model Mage_Tax_Model_Class */
        $model = Mage::getModel('Mage_Tax_Model_Class')
            ->load($customerClasses[0]);
        $this->setExpectedException('Mage_Core_Exception', 'You cannot delete this tax class because it is used in' .
            ' Tax Rules. You have to delete the rules it is used in first.');
        $model->checkClassCanBeDeleted();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Tax/_files/tax_classes.php
     */
    public function testCheckClassCanBeDeletedProductClassUsedInTaxRule()
    {
        /** @var $registry Mage_Core_Model_Registry */
        $registry = Mage::getObjectManager()->get('Mage_Core_Model_Registry');
        /** @var $taxRule Mage_Tax_Model_Calculation_Rule */
        $taxRule = $registry->registry('_fixture/Mage_Tax_Model_Calculation_Rule');
        $productClasses = $taxRule->getProductTaxClasses();

        /** @var $model Mage_Tax_Model_Class */
        $model = Mage::getModel('Mage_Tax_Model_Class')
            ->load($productClasses[0]);
        $this->setExpectedException('Mage_Core_Exception', 'You cannot delete this tax class because it is used in' .
            ' Tax Rules. You have to delete the rules it is used in first.');
        $model->checkClassCanBeDeleted();
    }
}
