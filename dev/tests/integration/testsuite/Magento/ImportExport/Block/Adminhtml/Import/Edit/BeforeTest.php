<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\ImportExport\Block\Adminhtml\Import\Edit\Before
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Edit;

class BeforeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test model
     *
     * @var \Magento\ImportExport\Block\Adminhtml\Import\Edit\Before
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create('Magento\ImportExport\Block\Adminhtml\Import\Edit\Before');
    }

    /**
     * Test for getEntityBehaviors method
     *
     * @covers \Magento\ImportExport\Block\Adminhtml\Import\Edit\Before::getEntityBehaviors
     */
    public function testGetEntityBehaviors()
    {
        $actualEntities = $this->_model->getEntityBehaviors();
        $expectedEntities = '{"catalog_product":"basic_behavior","customer_finance":"custom_behavior",' .
            '"customer_composite":"basic_behavior","customer":"custom_behavior","customer_address":"custom_behavior"}';
        $this->assertEquals($expectedEntities, $actualEntities);
    }

    /**
     * Test for getUniqueBehaviors method
     *
     * @covers \Magento\ImportExport\Block\Adminhtml\Import\Edit\Before::getUniqueBehaviors
     */
    public function testGetUniqueBehaviors()
    {
        $actualBehaviors = $this->_model->getUniqueBehaviors();
        $expectedBehaviors = '["basic_behavior","custom_behavior"]';
        $this->assertEquals($expectedBehaviors, $actualBehaviors);
    }
}
