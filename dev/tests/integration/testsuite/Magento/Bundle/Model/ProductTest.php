<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * As far none class is present as separate bundle product,
 * this test is clone of \Magento\Catalog\Model\Product with product type "bundle"
 */
namespace Magento\Bundle\Model;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->_model->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
    }

    public function testGetTypeId()
    {
        $this->assertEquals(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE, $this->_model->getTypeId());
    }

    public function testGetSetTypeInstance()
    {
        // model getter
        $typeInstance = $this->_model->getTypeInstance();
        $this->assertInstanceOf('Magento\Bundle\Model\Product\Type', $typeInstance);
        $this->assertSame($typeInstance, $this->_model->getTypeInstance());

        // singleton getter
        $otherProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $otherProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->assertSame($typeInstance, $otherProduct->getTypeInstance());

        // model setter
        $customTypeInstance = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Bundle\Model\Product\Type'
        );
        $this->_model->setTypeInstance($customTypeInstance);
        $this->assertSame($customTypeInstance, $this->_model->getTypeInstance());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testCRUD()
    {
        $this->_model->setTypeId(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        )->setAttributeSetId(
            4
        )->setName(
            'Bundle Product'
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
        );
        $crud = new \Magento\TestFramework\Entity($this->_model, ['sku' => uniqid()]);
        $crud->testCrud();
    }

    public function testGetPriceModel()
    {
        $this->_model->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $type = $this->_model->getPriceModel();
        $this->assertInstanceOf('Magento\Bundle\Model\Product\Price', $type);
        $this->assertSame($type, $this->_model->getPriceModel());
    }

    public function testIsComposite()
    {
        $this->assertTrue($this->_model->isComposite());
    }
}
