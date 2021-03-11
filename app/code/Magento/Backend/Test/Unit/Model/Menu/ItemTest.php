<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Item
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_aclMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_menuFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_moduleManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
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
        'dependsOnModule' => 'Magento_Backend',
        'dependsOnConfig' => 'system/config/isEnabled',
        'toolTip' => 'Item tooltip',
    ];

    protected function setUp(): void
    {
        $this->_aclMock = $this->createMock(\Magento\Framework\AuthorizationInterface::class);
        $this->_scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_menuFactoryMock = $this->createPartialMock(\Magento\Backend\Model\MenuFactory::class, ['create']);
        $this->_urlModelMock = $this->createMock(\Magento\Backend\Model\Url::class);
        $this->_moduleManager = $this->createMock(\Magento\Framework\Module\Manager::class);
        $validatorMock = $this->createMock(\Magento\Backend\Model\Menu\Item\Validator::class);
        $validatorMock->expects($this->any())->method('validate');
        $this->_moduleListMock = $this->createMock(\Magento\Framework\Module\ModuleListInterface::class);

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
        )->willReturn(
            'Url'
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
        $this->_moduleManager->expects($this->once())->method('isOutputEnabled')->willReturn(false);
        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsTrueIfModuleDependenciesFail()
    {
        $this->_moduleManager->expects($this->once())->method('isOutputEnabled')->willReturn(true);

        $this->_moduleListMock->expects($this->once())->method('has')->willReturn(true);

        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsTrueIfConfigDependenciesFail()
    {
        $this->_moduleManager->expects($this->once())->method('isOutputEnabled')->willReturn(true);

        $this->_moduleListMock->expects($this->once())->method('has')->willReturn(true);

        $this->assertTrue($this->_model->isDisabled());
    }

    public function testIsDisabledReturnsFalseIfNoDependenciesFail()
    {
        $this->_moduleManager->expects($this->once())->method('isOutputEnabled')->willReturn(true);

        $this->_moduleListMock->expects($this->once())->method('has')->willReturn(true);

        $this->_scopeConfigMock->expects($this->once())->method('isSetFlag')->willReturn(true);

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
        )->willReturn(
            true
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
        $menuMock = $this->createMock(\Magento\Backend\Model\Menu::class);

        $this->_menuFactoryMock->expects($this->once())->method('create')->willReturn($menuMock);

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
        $menuMock = $this->createMock(\Magento\Backend\Model\Menu::class);
        $this->_menuFactoryMock->method('create')->willReturn($menuMock);
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
        $menuMock = $this->createMock(\Magento\Backend\Model\Menu::class);
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
