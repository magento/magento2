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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_sourceEntities = array(
        'entity_1' => array('code' => 'behavior_1', 'token' => 'Some_Random_First_Class'),
        'entity_2' => array('code' => 'behavior_2', 'token' => 'Some_Random_Second_Class')
    );

    /**
     * Expected entity behaviors
     *
     * @var array
     */
    protected $_expectedEntities = array('entity_1' => 'behavior_1', 'entity_2' => 'behavior_2');

    /**
     * Source unique behaviors
     *
     * @var array
     */
    protected $_sourceBehaviors = array(
        'behavior_1' => 'Some_Random_First_Class',
        'behavior_2' => 'Some_Random_Second_Class'
    );

    /**
     * Expected unique behaviors
     *
     * @var array
     */
    protected $_expectedBehaviors = array('behavior_1', 'behavior_2');

    protected function setUp()
    {
        $coreHelper = $this->getMock('Magento\Core\Helper\Data', array('jsonEncode'), array(), '', false, false);
        $coreHelper->expects(
            $this->any()
        )->method(
            'jsonEncode'
        )->will(
            $this->returnCallback(array($this, 'jsonEncodeCallback'))
        );

        $importModel = $this->getMock(
            'Magento\ImportExport\Model\Import',
            array('getEntityBehaviors', 'getUniqueEntityBehaviors'),
            array(),
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

        $arguments = array(
            'coreData' => $coreHelper,
            'importModel' => $importModel,
            'urlBuilder' => $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false)
        );
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create('Magento\ImportExport\Block\Adminhtml\Import\Edit\Before', $arguments);
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Callback method for \Magento\Core\Helper\Data::jsonEncode
     *
     * @param mixed $data
     * @return string
     */
    public function jsonEncodeCallback($data)
    {
        return \Zend_Json::encode($data);
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
