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

    /**
     * Source entity behaviors
     *
     * @var array
     */
    protected $_sourceEntities = [
        'entity_1' => ['code' => 'behavior_1', 'token' => 'Some_Random_First_Class'],
        'entity_2' => ['code' => 'behavior_2', 'token' => 'Some_Random_Second_Class'],
    ];

    /**
     * Expected entity behaviors
     *
     * @var array
     */
    protected $_expectedEntities = ['entity_1' => 'behavior_1', 'entity_2' => 'behavior_2'];

    /**
     * Source unique behaviors
     *
     * @var array
     */
    protected $_sourceBehaviors = [
        'behavior_1' => 'Some_Random_First_Class',
        'behavior_2' => 'Some_Random_Second_Class',
    ];

    /**
     * Expected unique behaviors
     *
     * @var array
     */
    protected $_expectedBehaviors = ['behavior_1', 'behavior_2'];

    protected function setUp()
    {
        $importModel = $this->getMock(
            'Magento\ImportExport\Model\Import',
            ['getEntityBehaviors', 'getUniqueEntityBehaviors'],
            [],
            '',
            false
        );
        $importModel->expects(
            $this->any()
        )->method(
            'getEntityBehaviors'
        )->will(
            $this->returnValue($this->_sourceEntities)
        );
        $importModel->expects(
            $this->any()
        )->method(
            'getUniqueEntityBehaviors'
        )->will(
            $this->returnValue($this->_sourceBehaviors)
        );

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create(
            'Magento\ImportExport\Block\Adminhtml\Import\Edit\Before',
            [
                'importModel' => $importModel,
            ]
        );
    }

    /**
     * Test for getEntityBehaviors method
     *
     * @covers \Magento\ImportExport\Block\Adminhtml\Import\Edit\Before::getEntityBehaviors
     */
    public function testGetEntityBehaviors()
    {
        $actualEntities = $this->_model->getEntityBehaviors();
        $expectedEntities = \Zend_Json::encode($this->_expectedEntities);
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
        $expectedBehaviors = \Zend_Json::encode($this->_expectedBehaviors);
        $this->assertEquals($expectedBehaviors, $actualBehaviors);
    }
}
