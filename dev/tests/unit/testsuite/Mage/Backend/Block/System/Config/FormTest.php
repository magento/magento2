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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Block_System_Config_FormTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_System_Config_Form
     */
    protected $_object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_systemConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formMock;
    
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fieldFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;
    
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formFactoryMock;
    
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendConfigMock;
    
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fieldsetFactoryMock;

    protected function setUp()
    {
        $this->_systemConfigMock = $this->getMock('Mage_Backend_Model_Config_Structure',
            array(), array(), '', false, false
        );

        $requestMock = $this->getMock('Mage_Core_Controller_Request_Http',
            array(), array(), '', false, false
        );
        $requestParams = array(
            array('website', '', 'website_code'),
            array('section', '', 'section_code'),
            array('store', '', 'store_code'),
        );
        $requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($requestParams));

        $helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false, false);

        $layoutMock = $this->getMock('Mage_Core_Model_Layout',
            array(), array(), '', false, false
        );
        $layoutMock->expects($this->any())->method('helper')->will($this->returnValue($helperMock));
        $helperMock->expects($this->any())->method('__')->will($this->returnArgument(0));

        $this->_urlModelMock = $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false, false);
        $configFactoryMock = $this->getMock('Mage_Backend_Model_Config_Factory', array(), array(), '', false, false);
        $this->_formFactoryMock = $this->getMock('Varien_Data_Form_Factory', array(), array(), '', false, false);
        $cloneFactoryMock = $this->getMock('Mage_Backend_Model_Config_Clone_Factory',
            array(), array(), '', false, false
        );
        $this->_fieldsetFactoryMock = $this->getMock('Mage_Backend_Block_System_Config_Form_Fieldset_Factory',
            array(), array(), '', false, false
        );
        $this->_fieldFactoryMock = $this->getMock('Mage_Backend_Block_System_Config_Form_Field_Factory',
            array(), array(), '', false, false
        );
        $coreConfigMock = $this->getMock('Mage_Core_Model_Config',
            array(), array(), '', false, false
        );

        $this->_backendConfigMock = $this->getMock('Mage_Backend_Model_Config',
            array(), array(), '', false, false
        );

        $configFactoryMock->expects($this->once())->method('create')
            ->with(array('section' => 'section_code', 'website' => 'website_code', 'store' => 'store_code'))
            ->will($this->returnValue($this->_backendConfigMock));

        $this->_backendConfigMock->expects($this->once())->method('load')
            ->will($this->returnValue(array('section1/group1/field1' => 'some_value')));

        $this->_formMock = $this->getMock('Varien_Data_Form',
            array('setParent', 'setBaseUrl', 'addFieldset'), array(), '', false, false
        );
        $data = array(
            'request' => $requestMock,
            'layout' => $layoutMock,
            'urlBuilder' => $this->_urlModelMock,
            'configStructure' => $this->_systemConfigMock,
            'configFactory' => $configFactoryMock,
            'formFactory' => $this->_formFactoryMock,
            'cloneModelFactory' => $cloneFactoryMock,
            'fieldsetFactory' => $this->_fieldsetFactoryMock,
            'fieldFactory' => $this->_fieldFactoryMock,
            'coreConfig' => $coreConfigMock,
        );

        $helper = new Magento_Test_Helper_ObjectManager($this);
        $this->_object = $helper->getBlock('Mage_Backend_Block_System_Config_Form', $data);
        $this->_object->setData('scope_id', 1);
    }

    public function testInitFormWithoutSection()
    {
        $this->_formFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_formMock));
        $this->_formMock->expects($this->once())->method('setParent')->with($this->_object);
        $this->_formMock->expects($this->once())->method('setBaseUrl')->with('base_url');
        $this->_urlModelMock->expects($this->any())->method('getBaseUrl')->will($this->returnValue('base_url'));

        $this->_systemConfigMock->expects($this->once())->method('getElement')
            ->with('section_code')->will($this->returnValue(null));

        $this->_object->initForm();
        $this->assertEquals($this->_formMock, $this->_object->getForm());
    }

    public function testInitGroup()
    {
        $this->_formFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_formMock));
        $this->_formMock->expects($this->once())->method('setParent')->with($this->_object);
        $this->_formMock->expects($this->once())->method('setBaseUrl')->with('base_url');
        $this->_urlModelMock->expects($this->any())->method('getBaseUrl')->will($this->returnValue('base_url'));

        $fieldsetRendererMock = $this->getMock('Mage_Backend_Block_System_Config_Form_Fieldset',
            array(), array(), '', false, false
        );
        $this->_fieldsetFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($fieldsetRendererMock));

        $cloneModelMock = $this->getMock('Mage_Backend_Model_Config',
            array('getPrefixes'), array(), '', false, false
        );

        $cloneModelMock->expects($this->once())->method('getPrefixes')->will($this->returnValue(array()));

        $groupMock = $this->getMock('Mage_Backend_Model_Config_Structure_Element_Group',
            array(), array(), '', false, false
        );
        $groupMock->expects($this->once())->method('getFrontendModel')->will($this->returnValue(false));
        $groupMock->expects($this->any())->method('getPath')->will($this->returnValue('section_id_group_id'));
        $groupMock->expects($this->once())->method('getLabel')->will($this->returnValue('label'));
        $groupMock->expects($this->once())->method('getComment')->will($this->returnValue('comment'));
        $groupMock->expects($this->once())->method('isExpanded')->will($this->returnValue(false));
        $groupMock->expects($this->once())->method('populateFieldset');
        $groupMock->expects($this->once())->method('shouldCloneFields')->will($this->returnValue(true));
        $groupMock->expects($this->once())->method('getCloneModel')->will($this->returnValue($cloneModelMock));
        $groupMock->expects($this->once())
            ->method('getDependencies')->with('store_code')->will($this->returnValue(array()));

        $sectionMock = $this->getMock('Mage_Backend_Model_Config_Structure_Element_Section',
            array(), array(), '', false, false
        );

        $sectionMock->expects($this->once())->method('isVisible')->will($this->returnValue(true));
        $sectionMock->expects($this->once())->method('getChildren')->will($this->returnValue(array($groupMock)));

        $this->_systemConfigMock->expects($this->once())->method('getElement')
            ->with('section_code')->will($this->returnValue($sectionMock));

        $formFieldsetMock = $this->getMock('Varien_Data_Form_Element_Fieldset',
            array(), array(), '', false, false
        );

        $params = array(
            'legend' => 'label',
            'comment' => 'comment',
            'expanded' => false,
        );
        $this->_formMock->expects($this->once())->method('addFieldset')->with('section_id_group_id', $params)
            ->will($this->returnValue($formFieldsetMock));
        $this->_object->initForm();
    }

    public function testInitFields()
    {
        // Parameters initialization
        $fieldsetMock = $this->getMock('Varien_Data_Form_Element_Fieldset', array(), array(), '', false, false);
        $groupMock = $this->getMock('Mage_Backend_Model_Config_Structure_Element_Group',
            array(), array(), '', false, false
        );
        $sectionMock = $this->getMock('Mage_Backend_Model_Config_Structure_Element_Section',
            array(), array(), '', false, false
        );
        $fieldPrefix = 'fieldPrefix';
        $labelPrefix = 'labelPrefix';

        // Field Renderer Mock configuration
        $fieldRendererMock = $this->getMock('Mage_Backend_Block_System_Config_Form_Field',
            array(), array(), '', false, false
        );
        $this->_fieldFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($fieldRendererMock));

        $this->_backendConfigMock->expects($this->once())->method('extendConfig')
            ->with('some/config/path', false, array('section1/group1/field1' => 'some_value'))
            ->will($this->returnArgument(2));

        
        // Field mock configuration
        $fieldMock = $this->getMock('Mage_Backend_Model_Config_Structure_Element_Field',
            array(), array(), '', false, false
        );
        $fieldMock->expects($this->any())->method('getPath')
            ->will($this->returnValue('section1/group1/field1'));
        $fieldMock->expects($this->any())->method('getGroupPath')->will($this->returnValue('some/config/path'));
        $fieldMock->expects($this->once())->method('getSectionId')->will($this->returnValue('some_section'));

        $fieldMock->expects($this->once())->method('hasBackendModel')->will($this->returnValue(false));
        $fieldMock->expects($this->once())->method('getDependencies')
            ->with($fieldPrefix)->will($this->returnValue(array()));
        $fieldMock->expects($this->any())->method('getType')->will($this->returnValue('field'));
        $fieldMock->expects($this->once())->method('getLabel')->will($this->returnValue('label'));
        $fieldMock->expects($this->once())->method('getComment')->will($this->returnValue('comment'));
        $fieldMock->expects($this->once())->method('getTooltip')->will($this->returnValue('tooltip'));
        $fieldMock->expects($this->once())->method('getHint')->will($this->returnValue('hint'));
        $fieldMock->expects($this->once())->method('getFrontendClass')->will($this->returnValue('frontClass'));
        $fieldMock->expects($this->once())->method('showInDefault')->will($this->returnValue(false));
        $fieldMock->expects($this->any())->method('showInWebsite')->will($this->returnValue(false));
        $fieldMock->expects($this->once())->method('getData')->will($this->returnValue('fieldData'));


        $fields = array($fieldMock);
        $groupMock->expects($this->once())->method('getChildren')->will($this->returnValue($fields));

        $sectionMock->expects($this->once())->method('getId')->will($this->returnValue('section1'));

        $formFieldMock = $this->getMockForAbstractClass('Varien_Data_Form_Element_Abstract',
            array(), '', false, false, true, array('setRenderer')
        );

        $params = array(
            'name' => 'groups[group1][fields][fieldPrefixfield1][value]',
            'label' => 'label',
            'comment' => 'comment',
            'tooltip' => 'tooltip',
            'hint' => 'hint',
            'value' => 'some_value',
            'inherit' => false,
            'class' => 'frontClass',
            'field_config' => 'fieldData',
            'scope' => 'stores',
            'scope_id' => 1,
            'scope_label' => '[GLOBAL]',
            'can_use_default_value' => false,
            'can_use_website_value' => false,
        );

        $formFieldMock->expects($this->once())->method('setRenderer')->with($fieldRendererMock);

        $fieldsetMock->expects($this->once())->method('addField')
            ->with('section1_group1_field1', 'field', $params)
            ->will($this->returnValue($formFieldMock));

        $fieldMock->expects($this->once())->method('populateInput');

        $this->_object->initFields($fieldsetMock, $groupMock, $sectionMock, $fieldPrefix, $labelPrefix);
    }
}
