<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Eav;

/**
 * Test for \Magento\Catalog\Model\ResourceModel\Eav\Attribute.
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        );
    }

    /**
     * Test Create -> Read -> Update -> Delete attribute operations.
     *
     * @return void
     */
    public function testCRUD()
    {
        $this->_model->setAttributeCode(
            'test'
        )->setEntityTypeId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Eav\Model\Config::class
            )->getEntityType(
                'catalog_product'
            )->getId()
        )->setFrontendLabel(
            'test'
        )->setIsUserDefined(1);
        $crud = new \Magento\TestFramework\Entity($this->_model, ['frontend_label' => uniqid()]);
        $crud->testCrud();
    }
}
