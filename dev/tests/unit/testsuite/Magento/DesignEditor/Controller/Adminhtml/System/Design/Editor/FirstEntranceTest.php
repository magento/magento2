<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test backend controller for the design editor
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

class FirstEntranceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->any())->method('setActionName')->will($this->returnSelf());

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        /** @var $layoutMock \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject */
        $layoutMock = $this->getMock(
            'Magento\Framework\View\Layout',
            [
                'getBlock',
                'getUpdate',
                'addHandle',
                'load',
                'generateXml',
                'getNode',
                'generateElements',
                'getMessagesBlock'
            ],
            [],
            '',
            false
        );
        /** @var $layoutMock \Magento\Framework\View\LayoutInterface */
        $layoutMock->expects($this->any())->method('generateXml')->will($this->returnSelf());
        $layoutMock->expects(
            $this->any()
        )->method(
            'getNode'
        )->will(
            $this->returnValue(new \Magento\Framework\Simplexml\Element('<root />'))
        );
        $blockMessage = $this->getMock(
            'Magento\Framework\View\Element\Messages',
            ['addMessages', 'setEscapeMessageFlag', 'addStorageType'],
            [],
            '',
            false
        );
        $layoutMock->expects($this->any())->method('getMessagesBlock')->will($this->returnValue($blockMessage));

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\Template',
            ['setActive', 'getMenuModel', 'getParentItems'],
            [],
            '',
            false
        );
        $blockMock->expects($this->any())->method('getMenuModel')->will($this->returnSelf());
        $blockMock->expects($this->any())->method('getParentItems')->will($this->returnValue([]));

        $layoutMock->expects($this->any())->method('getBlock')->will($this->returnValue($blockMock));
        $layoutMock->expects($this->any())->method('getUpdate')->will($this->returnSelf());

        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor',
            [
                'request' => $request,
                'objectManager' => $this->objectManagerMock,
                'layout' => $layoutMock,
                'invokeArgs' => [
                    'helper' => $this->getMock('Magento\Backend\Helper\Data', [], [], '', false),
                    'session' => $this->getMock('Magento\Backend\Model\Session', [], [], '', false),
                ]
            ]
        );

        $this->model = $objectManagerHelper->getObject(
            'Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\FirstEntrance',
            $constructArguments
        );
    }

    /**
     * Return mocked theme collection factory model
     *
     * @param int $countCustomization
     * @return \Magento\Core\Model\Resource\Theme\CollectionFactory
     */
    protected function getThemeCollectionFactory($countCustomization)
    {
        $themeCollectionMock = $this->getMockBuilder(
            'Magento\Core\Model\Resource\Theme\Collection'
        )->disableOriginalConstructor()->setMethods(
            ['addTypeFilter', 'getSize']
        )->getMock();

        $themeCollectionMock->expects(
            $this->once()
        )->method(
            'addTypeFilter'
        )->with(
            \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
        )->will(
            $this->returnValue($themeCollectionMock)
        );

        $themeCollectionMock->expects($this->once())->method('getSize')->will($this->returnValue($countCustomization));

        /** @var \Magento\Core\Model\Resource\Theme\CollectionFactory $collectionFactory */
        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $collectionFactory->expects($this->once())->method('create')->will($this->returnValue($themeCollectionMock));

        return $collectionFactory;
    }

    /**
     * @dataProvider firstEntranceActionDataProvider
     */
    public function testFirstEntranceAction($countCustomization)
    {
        $this->objectManagerMock->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValueMap($this->getObjectManagerMap($countCustomization))
        );
        $this->assertNull($this->model->execute());
    }

    /**
     * @return array
     */
    public function firstEntranceActionDataProvider()
    {
        return [[3], [0]];
    }

    /**
     * @param int $countCustomization
     * @return array
     */
    protected function getObjectManagerMap($countCustomization)
    {
        $translate = $this->getMock('Magento\Framework\TranslateInterface', [], [], '', false);
        $translate->expects($this->any())->method('translate')->will($this->returnSelf());

        $storeManager = $this->getMock(
            'Magento\Store\Model\StoreManager',
            ['getStore', 'getBaseUrl'],
            [],
            '',
            false
        );
        $storeManager->expects($this->any())->method('getStore')->will($this->returnSelf());

        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $authMock = $this->getMock('Magento\Framework\AuthorizationInterface');
        $authMock->expects($this->any())->method('filterAclNodes')->will($this->returnSelf());
        $backendSession = $this->getMock(
            'Magento\Backend\Model\Session',
            ['getMessages', 'getEscapeMessages'],
            [],
            '',
            false
        );
        $backendSession->expects(
            $this->any()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue($this->getMock('Magento\Framework\Message\Collection', [], [], '', false))
        );

        $inlineMock = $this->getMock('Magento\Framework\Translate\Inline', [], [], '', false);
        $aclFilterMock = $this->getMock('Magento\Backend\Model\Layout\Filter\Acl', [], [], '', false);

        return [
            [
                'Magento\Core\Model\Resource\Theme\CollectionFactory',
                $this->getThemeCollectionFactory($countCustomization),
            ],
            ['Magento\Framework\TranslateInterface', $translate],
            ['Magento\Framework\App\Config\ScopeConfigInterface', $configMock],
            ['Magento\Framework\Event\ManagerInterface', $eventManager],
            ['Magento\Store\Model\StoreManager', $storeManager],
            ['Magento\Framework\AuthorizationInterface', $authMock],
            ['Magento\Backend\Model\Session', $backendSession],
            ['Magento\Framework\Translate\Inline', $inlineMock],
            ['Magento\Backend\Model\Layout\Filter\Acl', $aclFilterMock]
        ];
    }
}
