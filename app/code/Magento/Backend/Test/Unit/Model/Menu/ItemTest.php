<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Item
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleListMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /**
     * @var array
     */
    protected $_params = [
        'id' => 'item',
        'title' => 'Item Title',
        'action' => '/system/config',
        'resource' => 'Magento_Config::config',
        'depends_on_module' => 'Magento_Backend',
        'depends_on_config' => 'system/config/isEnabled',
        'tooltip' => 'Item tooltip',
    ];

    protected function setUp()
    {
        $this->_aclMock = $this->getMock(\Magento\Framework\AuthorizationInterface::class);
        $this->_scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_menuFactoryMock = $this->getMock(
            \Magento\Backend\Model\MenuFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_urlModelMock = $this->getMock(\Magento\Backend\Model\Url::class, [], [], '', false);
        $this->_moduleManager = $this->getMock(\Magento\Framework\Module\Manager::class, [], [], '', false);
        $validatorMock = $this->getMock(\Magento\Backend\Model\Menu\Item\Validator::class);
        $validatorMock->expects($this->any())->method('validate');
        $this->_moduleListMock = $this->getMock(\Magento\Framework\Module\ModuleListInterface::class);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $this->objectManager->getObject(
            \Magento\Backend\Model\Menu\Item::class,
            [
                'validator' => $validatorMock,
                'authorization' => $this->_aclMock,
                'scopeConfig' => $this->_scopeConfigMock,
                'menuFactory' => $this->_menuFactoryMock,
                'urlModel' => $this->_urlModelMock,
                'moduleList' => $this->_moduleListMock,
                'moduleManager' => $this->_moduleManager,
                'data' => $this->_params
            ]
        );
    }

    public function testGetUrlWithEmptyActionReturnsHashSign()
    {
        $this->_params['action'] = '';
        $item = $this->objectManager->getObject(
            \Magento\Backend\Model\Menu\Item::class,
            ['menuFactory' => $this->_menuFactoryMock, 'data' => $this->_params]
        );
        $this->assertEquals('#', $item->getUrl());
    }

    public function testGetUrlWithValidActionReturnsUrl()
    {
        $this->_urlModelMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('/system/config')
        )->will(
            $this->returnValue('Url')
        );
        $this->assertEquals('Url', $this->_model->getUrl());
    }

    public function testHasClickCallbackReturnsFalseIfItemHasAction()
    {
        $this->assertFalse($this->_model->hasClickCallback());
    }

    public function testHasClickCallbackReturnsTrueIfItemHasNoAction()
    {
        $this->_params['action'] = '';
        $item = $this->objectManager->getObject(
            \Magento\Backend\Model\Menu\Item::class,
            ['menuFactory' => $this->_menuFactoryMock, 'data' => $this->_params]
        );
        $this->assertTrue($item->hasClickCallback());
    }

    public function testGetClickCallbackReturnsStoppingJsIfItemDoesntHaveAction()
    {
        $this->_params['action'] = '';
        $item = $this->objectManager->getObject(
            \Magento\Backend\Model\Menu\Item::class,
            ['menuFactory' => $this->_menuFactoryMock, 'data' => $this->_params]
        );
        $this->assertEquals('return false;', $item->getClickCallback());
    }

    public function testGetClickCallbackReturnsEmptyStringIfItemHasAction()
    {
        $this->assertEquals('', $this->_model->getClickCallback());
    }

    public function testIsDisabledReturnsTrueIfModuleOutputIsDisabled()
    {
        $this->_moduleManager->expects($this->once())->method('isOutputEnabled')->will($this->returnValue(false));
        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsTrueIfModuleDependenciesFail()
    {
        $this->_moduleManager->expects($this->once())->method('isOutputEnabled')->will($this->returnValue(true));

        $this->_moduleListMock->expects($this->once())->method('has')->will($this->returnValue(true));

        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsTrueIfConfigDependenciesFail()
    {
        $this->_moduleManager->expects($this->once())->method('isOutputEnabled')->will($this->returnValue(true));

        $this->_moduleListMock->expects($this->once())->method('has')->will($this->returnValue(true));

        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsFalseIfNoDependenciesFail()
    {
        $this->_moduleManager->expects($this->once())->method('isOutputEnabled')->will($this->returnValue(true));

        $this->_moduleListMock->expects($this->once())->method('has')->will($this->returnValue(true));

        $this->_scopeConfigMock->expects($this->once())->method('isSetFlag')->will($this->returnValue(true));

        $this->assertFalse($this->_model->isDisabled());
    }

    public function testIsAllowedReturnsTrueIfResourceIsAvailable()
    {
        $this->_aclMock->expects(
            $this->once()
        )->method(
            'isAllowed'
        )->with(
            'Magento_Config::config'
        )->will(
            $this->returnValue(true)
        );
        $this->assertTrue($this->_model->isAllowed());
    }

    public function testIsAllowedReturnsFalseIfResourceIsNotAvailable()
    {
        $this->_aclMock->expects(
            $this->once()
        )->method(
            'isAllowed'
        )->with(
            'Magento_Config::config'
        )->will(
            $this->throwException(new \Magento\Framework\Exception\LocalizedException(__('Error')))
        );
        $this->assertFalse($this->_model->isAllowed());
    }

    public function testGetChildrenCreatesSubmenuOnFirstCall()
    {
        $menuMock = $this->getMock(\Magento\Backend\Model\Menu::class, [], [], '', false);

        $this->_menuFactoryMock->expects($this->once())->method('create')->will($this->returnValue($menuMock));

        $this->_model->getChildren();
        $this->_model->getChildren();
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider toArrayDataProvider
     */
    public function testToArray(array $data, array $expected)
    {
        $menuMock = $this->getMock(\Magento\Backend\Model\Menu::class, [], [], '', false);
        $this->_menuFactoryMock->method('create')->will($this->returnValue($menuMock));
        $menuMock->method('toArray')
            ->willReturn($data['sub_menu']);

        $model = $this->objectManager->getObject(
            \Magento\Backend\Model\Menu\Item::class,
            [
                'authorization' => $this->_aclMock,
                'scopeConfig' => $this->_scopeConfigMock,
                'menuFactory' => $this->_menuFactoryMock,
                'urlModel' => $this->_urlModelMock,
                'moduleList' => $this->_moduleListMock,
                'moduleManager' => $this->_moduleManager,
                'data' => $data
            ]
        );
        $this->assertEquals($expected, $model->toArray());
    }

    /**
     * @return array
     */
    public function toArrayDataProvider()
    {
        return include __DIR__ . '/../_files/menu_item_data.php';
    }

    /**
     * @param array $constructorData
     * @param array $populateFromData
     * @param array $expected
     * @dataProvider populateFromArrayDataProvider
     */
    public function testPopulateFromArray(
        array $constructorData,
        array $populateFromData,
        array $expected
    ) {
        $menuMock = $this->getMock(\Magento\Backend\Model\Menu::class, [], [], '', false);
        $this->_menuFactoryMock->method('create')->willReturn($menuMock);
        $menuMock->method('toArray')
            ->willReturn(['submenuArray']);

        $model = $this->objectManager->getObject(
            \Magento\Backend\Model\Menu\Item::class,
            [
                'authorization' => $this->_aclMock,
                'scopeConfig' => $this->_scopeConfigMock,
                'menuFactory' => $this->_menuFactoryMock,
                'urlModel' => $this->_urlModelMock,
                'moduleList' => $this->_moduleListMock,
                'moduleManager' => $this->_moduleManager,
                'data' => $constructorData
            ]
        );
        $model->populateFromArray($populateFromData);
        $this->assertEquals($expected, $model->toArray());
    }

    /**
     * @return array
     */
    public function populateFromArrayDataProvider()
    {
        return include __DIR__ . '/../_files/menu_item_constructor_data.php';
    }
}
