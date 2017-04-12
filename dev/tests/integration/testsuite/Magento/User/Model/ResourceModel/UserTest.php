<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\ResourceModel;

/**
 * @magentoAppArea adminhtml
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Model\ResourceModel\User */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\User\Model\ResourceModel\User::class
        );
    }

    public function testCountAll()
    {
        $this->assertSame(1, $this->_model->countAll());
    }

    public function testGetValidationRulesBeforeSave()
    {
        $rules = $this->_model->getValidationRulesBeforeSave();
        $this->assertInstanceOf('Zend_Validate_Interface', $rules);
    }
}
