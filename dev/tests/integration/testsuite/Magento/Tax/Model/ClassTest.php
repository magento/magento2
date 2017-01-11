<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;

class ClassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCheckClassCanBeDeletedCustomerClassAssertException()
    {
        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create(
            \Magento\Tax\Model\ClassModel::class
        )->getCollection()->setClassTypeFilter(
            \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER
        )->getFirstItem();

        $this->setExpectedException(\Magento\Framework\Exception\CouldNotDeleteException::class);
        $model->delete();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCheckClassCanBeDeletedProductClassAssertException()
    {
        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create(
            \Magento\Tax\Model\ClassModel::class
        )->getCollection()->setClassTypeFilter(
            \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
        )->getFirstItem();

        $this->_objectManager->create(
            \Magento\Catalog\Model\Product::class
        )->setTypeId(
            'simple'
        )->setAttributeSetId(
            4
        )->setName(
            'Simple Product'
        )->setSku(
            uniqid()
        )->setPrice(
            10
        )->setMetaTitle(
            'meta title'
        )->setMetaKeyword(
            'meta keyword'
        )->setMetaDescription(
            'meta description'
        )->setVisibility(
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
        )->setStatus(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        )->setTaxClassId(
            $model->getId()
        )->save();

        $this->setExpectedException(\Magento\Framework\Exception\CouldNotDeleteException::class);
        $model->delete();
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider classesDataProvider
     */
    public function testCheckClassCanBeDeletedPositiveResult($classType)
    {
        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create(\Magento\Tax\Model\ClassModel::class);
        $model->setClassName('TaxClass' . uniqid())->setClassType($classType)->isObjectNew(true);
        $model->save();

        $model->delete();
    }

    public function classesDataProvider()
    {
        return [
            [\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER],
            [\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testCheckClassCanBeDeletedCustomerClassUsedInTaxRule()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->_objectManager->get(\Magento\Framework\Registry::class);
        /** @var $taxRule \Magento\Tax\Model\Calculation\Rule */
        $taxRule = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $customerClasses = $taxRule->getCustomerTaxClasses();

        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create(\Magento\Tax\Model\ClassModel::class)->load($customerClasses[0]);
        $this->setExpectedException(
            \Magento\Framework\Exception\CouldNotDeleteException::class,
            'You cannot delete this tax class because it is used in' .
            ' Tax Rules. You have to delete the rules it is used in first.'
        );
        $model->delete();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testCheckClassCanBeDeletedProductClassUsedInTaxRule()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->_objectManager->get(\Magento\Framework\Registry::class);
        /** @var $taxRule \Magento\Tax\Model\Calculation\Rule */
        $taxRule = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $productClasses = $taxRule->getProductTaxClasses();

        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create(\Magento\Tax\Model\ClassModel::class)->load($productClasses[0]);
        $this->setExpectedException(
            \Magento\Framework\Exception\CouldNotDeleteException::class,
            'You cannot delete this tax class because it is used in' .
            ' Tax Rules. You have to delete the rules it is used in first.'
        );
        $model->delete();
    }
}
