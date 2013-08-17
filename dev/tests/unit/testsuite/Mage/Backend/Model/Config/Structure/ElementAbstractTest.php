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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Config_Structure_ElementAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Config_Structure_ElementAbstract
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryHelperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_applicationMock;

    protected function setUp()
    {
        $this->_factoryHelperMock = $this->getMock('Mage_Core_Model_Factory_Helper', array(), array(), '', false);
        $this->_applicationMock = $this->getMock('Mage_Core_Model_App', array(), array(), '', false);

        $this->_model = $this->getMockForAbstractClass(
            'Mage_Backend_Model_Config_Structure_ElementAbstract',
            array($this->_factoryHelperMock, $this->_applicationMock)
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_factoryHelperMock);
        unset($this->_applicationMock);
    }

    public function testGetId()
    {
        $this->assertEquals('', $this->_model->getId());
        $this->_model->setData(array('id' => 'someId'), 'someScope');
        $this->assertEquals('someId', $this->_model->getId());
    }

    public function testGetLabelTranslatesLabel()
    {
        $helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false);
        $helperMock->expects($this->once())->method('__')->with('some_label')
            ->will($this->returnValue('translatedLabel'));
        $this->_factoryHelperMock->expects($this->once())->method('get')->with('Mage_Module_Helper_Data')
            ->will($this->returnValue($helperMock));
        $this->assertEquals('', $this->_model->getLabel());
        $this->_model->setData(array('label' => 'some_label', 'module' => 'Mage_Module'), 'someScope');
        $this->assertEquals('translatedLabel', $this->_model->getLabel());
    }

    public function testGetCommentTranslatesComment()
    {
        $helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false);
        $helperMock->expects($this->once())->method('__')->with('some_comment')
            ->will($this->returnValue('translatedComment'));
        $this->_factoryHelperMock->expects($this->once())->method('get')->with('Mage_Module_Helper_Data')
            ->will($this->returnValue($helperMock));
        $this->assertEquals('', $this->_model->getLabel());
        $this->_model->setData(array('label' => 'some_comment', 'module' => 'Mage_Module'), 'someScope');
        $this->assertEquals('translatedComment', $this->_model->getLabel());
    }

    public function testGetFrontEndModel()
    {
        $this->_model->setData(array('frontend_model' => 'frontend_model_name'), 'store');
        $this->assertEquals('frontend_model_name', $this->_model->getFrontendModel());
    }

    public function testGetAttribute()
    {
        $this->_model->setData(array(
            'id' => 'elementId',
            'label' => 'Element Label',
            'customAttribute' => 'Custom attribute value'
        ), 'someScope');
        $this->assertEquals('elementId', $this->_model->getAttribute('id'));
        $this->assertEquals('Element Label', $this->_model->getAttribute('label'));
        $this->assertEquals('Custom attribute value', $this->_model->getAttribute('customAttribute'));
        $this->assertNull($this->_model->getAttribute('nonexistingAttribute'));
    }


    public function testIsVisibleReturnsTrueInSingleStoreModeForNonHiddenElements()
    {
        $this->_applicationMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(array('showInDefault' => 1, 'showInStore' => 0, 'showInWebsite' => 0),
            Mage_Backend_Model_Config_ScopeDefiner::SCOPE_DEFAULT);
        $this->assertTrue($this->_model->isVisible());
    }

    public function testIsVisibleReturnsFalseInSingleStoreModeForHiddenElements()
    {
        $this->_applicationMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(
            array('hide_in_single_store_mode' => 1, 'showInDefault' => 1, 'showInStore' => 0, 'showInWebsite' => 0),
            Mage_Backend_Model_Config_ScopeDefiner::SCOPE_DEFAULT
        );
        $this->assertFalse($this->_model->isVisible());
    }

    /**
     * Invisible elements is contains showInDefault="0" showInWebsite="0" showInStore="0"
     */
    public function testIsVisibleReturnsFalseInSingleStoreModeForInvisibleElements()
    {
        $this->_applicationMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(array('showInDefault' => 0, 'showInStore' => 0, 'showInWebsite' => 0),
            Mage_Backend_Model_Config_ScopeDefiner::SCOPE_DEFAULT
        );
        $this->assertFalse($this->_model->isVisible());
    }

    /**
     * @param array $settings
     * @param string $scope
     * @dataProvider isVisibleReturnsTrueForProperScopesDataProvider
     */
    public function testIsVisibleReturnsTrueForProperScopes($settings, $scope)
    {
        $this->_model->setData($settings, $scope);
        $this->assertTrue($this->_model->isVisible());
    }

    public function isVisibleReturnsTrueForProperScopesDataProvider()
    {
        return array(
            array(
                array('showInDefault' => 1, 'showInStore' => 0, 'showInWebsite' => 0),
                Mage_Backend_Model_Config_ScopeDefiner::SCOPE_DEFAULT
            ),
            array(
                array('showInDefault' => 0, 'showInStore' => 1, 'showInWebsite' => 0),
                Mage_Backend_Model_Config_ScopeDefiner::SCOPE_STORE
            ),
            array(
                array('showInDefault' => 0, 'showInStore' => 0, 'showInWebsite' => 1),
                Mage_Backend_Model_Config_ScopeDefiner::SCOPE_WEBSITE
            ),
        );
    }

    /**
     * @param array $settings
     * @param string $scope
     * @dataProvider isVisibleReturnsFalseForNonProperScopesDataProvider
     */
    public function testIsVisibleReturnsFalseForNonProperScopes($settings, $scope)
    {
        $this->_model->setData($settings, $scope);
        $this->assertFalse($this->_model->isVisible());
    }

    public function isVisibleReturnsFalseForNonProperScopesDataProvider()
    {
        return array(
            array(
                array('showInDefault' => 0, 'showInStore' => 1, 'showInWebsite' => 1),
                Mage_Backend_Model_Config_ScopeDefiner::SCOPE_DEFAULT
            ),
            array(
                array('showInDefault' => 1, 'showInStore' => 0, 'showInWebsite' => 1),
                Mage_Backend_Model_Config_ScopeDefiner::SCOPE_STORE
            ),
            array(
                array('showInDefault' => 1, 'showInStore' => 1, 'showInWebsite' => 0),
                Mage_Backend_Model_Config_ScopeDefiner::SCOPE_WEBSITE
            ),
        );
    }

    public function testGetClass()
    {
        $this->assertEquals('', $this->_model->getClass());
        $this->_model->setData(array('class' => 'some_class'), 'store');
        $this->assertEquals('some_class', $this->_model->getClass());
    }

    public function testGetPathBuildsFullPath()
    {
        $this->_model->setData(array('path' => 'section/group', 'id' => 'fieldId'), 'scope');
        $this->assertEquals('section/group/prefix_fieldId', $this->_model->getPath('prefix_'));
    }
}
