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
 * @package     Mage_ImportExport
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Block_Adminhtml_Import_Edit_Before
 */
class Mage_ImportExport_Block_Adminhtml_Import_Edit_BeforeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test model
     *
     * @var Mage_ImportExport_Block_Adminhtml_Import_Edit_Before
     */
    protected $_model;

    /**
     * Source entity behaviors
     *
     * @var array
     */
    protected $_sourceEntities = array(
        'entity_1' => array(
            'code'  => 'behavior_1',
            'token' => 'Some_Random_First_Class',
        ),
        'entity_2' => array(
            'code'  => 'behavior_2',
            'token' => 'Some_Random_Second_Class',
        ),
    );

    /**
     * Expected entity behaviors
     *
     * @var array
     */
    protected $_expectedEntities = array(
        'entity_1' => 'behavior_1',
        'entity_2' => 'behavior_2',
    );

    /**
     * Source unique behaviors
     *
     * @var array
     */
    protected $_sourceBehaviors = array(
        'behavior_1' => 'Some_Random_First_Class',
        'behavior_2' => 'Some_Random_Second_Class',
    );

    /**
     * Expected unique behaviors
     *
     * @var array
     */
    protected $_expectedBehaviors = array('behavior_1', 'behavior_2');

    public function setUp()
    {
        $coreHelper = $this->getMock('Mage_Core_Helper_Data', array('jsonEncode'), array(), '', false, false);
        $coreHelper->expects($this->any())
            ->method('jsonEncode')
            ->will($this->returnCallback(array($this, 'jsonEncodeCallback')));

        $importModel = $this->getMock(
            'Mage_ImportExport_Model_Import',
            array('getEntityBehaviors', 'getUniqueEntityBehaviors')
        );
        $importModel->staticExpects($this->any())
            ->method('getEntityBehaviors')
            ->will($this->returnValue($this->_sourceEntities));
        $importModel->staticExpects($this->any())
            ->method('getUniqueEntityBehaviors')
            ->will($this->returnValue($this->_sourceBehaviors));

        $arguments = array(
            'coreHelper'  => $coreHelper,
            'importModel' => $importModel,
            'urlBuilder' => $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false)
        );
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_model = $objectManagerHelper->getBlock('Mage_ImportExport_Block_Adminhtml_Import_Edit_Before',
            $arguments
        );
    }

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Callback method for Mage_Core_Helper_Data::jsonEncode
     *
     * @param mixed $data
     * @return string
     */
    public function jsonEncodeCallback($data)
    {
        return Zend_Json::encode($data);
    }

    /**
     * Test for getEntityBehaviors method
     *
     * @covers Mage_ImportExport_Block_Adminhtml_Import_Edit_Before::getEntityBehaviors
     */
    public function testGetEntityBehaviors()
    {
        $actualEntities = $this->_model->getEntityBehaviors();
        $expectedEntities = Zend_Json::encode($this->_expectedEntities);
        $this->assertEquals($expectedEntities, $actualEntities);
    }

    /**
     * Test for getUniqueBehaviors method
     *
     * @covers Mage_ImportExport_Block_Adminhtml_Import_Edit_Before::getUniqueBehaviors
     */
    public function testGetUniqueBehaviors()
    {
        $actualBehaviors = $this->_model->getUniqueBehaviors();
        $expectedBehaviors = Zend_Json::encode($this->_expectedBehaviors);
        $this->assertEquals($expectedBehaviors, $actualBehaviors);
    }
}
