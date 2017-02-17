<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Eav;

class AttributeTest extends \PHPUnit_Framework_TestCase
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
        );
        $crud = new \Magento\TestFramework\Entity($this->_model, ['frontend_label' => uniqid()]);
        $crud->testCrud();
    }
}
