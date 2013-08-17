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

class Mage_Backend_Model_Menu_ItemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Menu_Item
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validatorMock;

    /**
     * @var array
     */
    protected $_params = array(
        'id' => 'item',
        'title' => 'Item Title',
        'action' => '/system/config',
        'resource' => 'Mage_Backend::config',
        'dependsOnModule' => 'Mage_Backend',
        'dependsOnConfig' => 'system/config/isEnabled',
        'tooltip' => 'Item tooltip',
    );

    public function setUp()
    {
        $this->_aclMock = $this->getMock('Magento_AuthorizationInterface');
        $this->_appConfigMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_storeConfigMock = $this->getMock('Mage_Core_Model_Store_Config');
        $this->_menuFactoryMock = $this->getMock('Mage_Backend_Model_Menu_Factory', array(), array(), '', false);
        $this->_urlModelMock = $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false);
        $this->_helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false);
        $this->_validatorMock = $this->getMock('Mage_Backend_Model_Menu_Item_Validator');
        $this->_validatorMock->expects($this->any())
            ->method('validate');

        $this->_model = new Mage_Backend_Model_Menu_Item(
            $this->_validatorMock,
            $this->_aclMock,
            $this->_appConfigMock,
            $this->_storeConfigMock,
            $this->_menuFactoryMock,
            $this->_urlModelMock,
            $this->_helperMock,
            $this->_params
        );
    }

    public function testGetUrlWithEmptyActionReturnsHashSign()
    {
        $this->_params['action'] = '';
        $item = new Mage_Backend_Model_Menu_Item(
            $this->_validatorMock,
            $this->_aclMock,
            $this->_appConfigMock,
            $this->_storeConfigMock,
            $this->_menuFactoryMock,
            $this->_urlModelMock,
            $this->_helperMock,
            $this->_params
        );
        $this->assertEquals('#', $item->getUrl());
    }

    public function testGetUrlWithValidActionReturnsUrl()
    {
        $this->_urlModelMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->equalTo('/system/config')
            )
            ->will($this->returnValue('Url'));
        $this->assertEquals('Url', $this->_model->getUrl());
    }

    public function testHasClickCallbackReturnsFalseIfItemHasAction()
    {
        $this->assertFalse($this->_model->hasClickCallback());
    }

    public function testHasClickCallbackReturnsTrueIfItemHasNoAction()
    {
        $this->_params['action'] = '';
        $item = new Mage_Backend_Model_Menu_Item(
            $this->_validatorMock,
            $this->_aclMock,
            $this->_appConfigMock,
            $this->_storeConfigMock,
            $this->_menuFactoryMock,
            $this->_urlModelMock,
            $this->_helperMock,
            $this->_params
        );
        $this->assertTrue($item->hasClickCallback());
    }

    public function testGetClickCallbackReturnsStoppingJsIfItemDoesntHaveAction()
    {
        $this->_params['action'] = '';
        $item = new Mage_Backend_Model_Menu_Item(
            $this->_validatorMock,
            $this->_aclMock,
            $this->_appConfigMock,
            $this->_storeConfigMock,
            $this->_menuFactoryMock,
            $this->_urlModelMock,
            $this->_helperMock,
            $this->_params
        );
        $this->assertEquals('return false;', $item->getClickCallback());
    }

    public function testGetClickCallbackReturnsEmptyStringIfItemHasAction()
    {
        $this->assertEquals('', $this->_model->getClickCallback());
    }

    public function testIsDisabledReturnsTrueIfModuleOutputIsDisabled()
    {
        $this->_helperMock->expects($this->once())
            ->method('isModuleOutputEnabled')
            ->will($this->returnValue(false));
        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsTrueIfModuleDependenciesFail()
    {
        $this->_helperMock->expects($this->once())
            ->method('isModuleOutputEnabled')
            ->will($this->returnValue(true));

        $moduleConfig = new stdClass();
        $moduleConfig->{'Mage_Backend'} = $this->getMock('Mage_Test_Module_Config');
        $moduleConfig->{'Mage_Backend'}->expects($this->once())
            ->method('is')
            ->will($this->returnValue(false));

        $this->_appConfigMock->expects($this->once())
            ->method('getNode')
            ->will($this->returnValue($moduleConfig));

        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsTrueIfConfigDependenciesFail()
    {
        $this->_helperMock->expects($this->once())
            ->method('isModuleOutputEnabled')
            ->will($this->returnValue(true));

        $moduleConfig = new stdClass();
        $moduleConfig->{'Mage_Backend'} = $this->getMock('Mage_Test_Module_Config', array('is'));
        $moduleConfig->{'Mage_Backend'}->expects($this->once())
            ->method('is')
            ->will($this->returnValue(true));

        $this->_appConfigMock->expects($this->once())
            ->method('getNode')
            ->will($this->returnValue($moduleConfig));

        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsFalseIfNoDependenciesFail()
    {
        $this->_helperMock->expects($this->once())
            ->method('isModuleOutputEnabled')
            ->will($this->returnValue(true));

        $moduleConfig = new stdClass();
        $moduleConfig->{'Mage_Backend'} = $this->getMock('Mage_Test_Module_Config', array('is'));
        $moduleConfig->{'Mage_Backend'}->expects($this->once())
            ->method('is')
            ->will($this->returnValue(true));

        $this->_appConfigMock->expects($this->once())
            ->method('getNode')
            ->will($this->returnValue($moduleConfig));

        $this->_storeConfigMock->expects($this->once())
            ->method('getConfigFlag')
            ->will($this->returnValue(true));

        $this->assertFalse($this->_model->isDisabled());
    }

    public function testIsAllowedReturnsTrueIfResourceIsAvailable()
    {
        $this->_aclMock->expects($this->once())
            ->method('isAllowed')
            ->with('Mage_Backend::config')
            ->will($this->returnValue(true));
        $this->assertTrue($this->_model->isAllowed());
    }

    public function testIsAllowedReturnsFalseIfResourceIsNotAvailable()
    {
        $this->_aclMock->expects($this->once())
            ->method('isAllowed')
            ->with('Mage_Backend::config')
            ->will($this->throwException(new Magento_Exception()));
        $this->assertFalse($this->_model->isAllowed());
    }

    public function testGetChildrenCreatesSubmenuOnFirstCall()
    {
        $menuMock = $this->getMock('Mage_Backend_Model_Menu', array(), array(), '', false);

        $this->_menuFactoryMock->expects($this->once())
            ->method('getMenuInstance')
            ->will($this->returnValue($menuMock));

        $this->_model->getChildren();
        $this->_model->getChildren();
    }
}

class Mage_Test_Module_Config
{
    /**
     *
     * @SuppressWarnings(PHPMD.ShortMethodName))
     */
    public function is()
    {

    }
}
