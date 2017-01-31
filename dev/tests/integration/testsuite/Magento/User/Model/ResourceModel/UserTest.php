<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            'Magento\User\Model\ResourceModel\User'
        );
    }

    public function testCountAll()
    {
        $this->markTestSkipped('Test fails at Bamboo L4 plan. Skipped in scope of MAGETWO-63371.');

        $this->assertSame(1, $this->_model->countAll());
    }

    public function testGetValidationRulesBeforeSave()
    {
        $rules = $this->_model->getValidationRulesBeforeSave();
        $this->assertInstanceOf('Zend_Validate_Interface', $rules);
    }
}
