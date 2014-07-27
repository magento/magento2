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
 * Test backend controller for the design editor
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

class IndexTest extends \PHPUnit_Framework_TestCase
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
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');

        $request = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $request->expects($this->any())->method('setActionName')->will($this->returnSelf());

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        /** @var $layoutMock \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject */
        $layoutMock = $this->getMock(
            'Magento\Framework\View\Layout',
            array(
                'getBlock',
                'getUpdate',
                'addHandle',
                'load',
                'generateXml',
                'getNode',
                'generateElements',
                'getMessagesBlock'
            ),
            array(),
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
            array('addMessages', 'setEscapeMessageFlag', 'addStorageType'),
            array(),
            '',
            false
        );
        $layoutMock->expects($this->any())->method('getMessagesBlock')->will($this->returnValue($blockMessage));

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\Template',
            array('setActive', 'getMenuModel', 'getParentItems'),
            array(),
            '',
            false
        );
        $blockMock->expects($this->any())->method('getMenuModel')->will($this->returnSelf());
        $blockMock->expects($this->any())->method('getParentItems')->will($this->returnValue(array()));

        $layoutMock->expects($this->any())->method('getBlock')->will($this->returnValue($blockMock));
        $layoutMock->expects($this->any())->method('getUpdate')->will($this->returnSelf());

        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor',
            array(
                'request' => $request,
                'objectManager' => $this->objectManagerMock,
                'layout' => $layoutMock,
                'invokeArgs' => array(
                    'helper' => $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false),
                    'session' => $this->getMock('Magento\Backend\Model\Session', array(), array(), '', false)
                )
            )
        );

        $this->model = $objectManagerHelper->getObject(
            'Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Index',
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
            array('addTypeFilter', 'getSize')
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
            array('create'),
            array(),
            '',
            false
        );
        $collectionFactory->expects($this->once())->method('create')->will($this->returnValue($themeCollectionMock));

        return $collectionFactory;
    }

    /**
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($countCustomization)
    {
        $this->objectManagerMock->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValueMap($this->getObjectManagerMap($countCustomization, 'index'))
        );
        $this->assertNull($this->model->execute());
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        return array(array(4), array(0));
    }

    /**
     * @param int $countCustomization
     * @return array
     */
    protected function getObjectManagerMap($countCustomization)
    {
        $translate = $this->getMock('Magento\Framework\TranslateInterface', array(), array(), '', false);
        $translate->expects($this->any())->method('translate')->will($this->returnSelf());

        $storeManager = $this->getMock(
            'Magento\Store\Model\StoreManager',
            array('getStore', 'getBaseUrl'),
            array(),
            '',
            false
        );
        $storeManager->expects($this->any())->method('getStore')->will($this->returnSelf());

        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false);
        $configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $authMock = $this->getMock('Magento\Framework\AuthorizationInterface');
        $authMock->expects($this->any())->method('filterAclNodes')->will($this->returnSelf());
        $backendSession = $this->getMock(
            'Magento\Backend\Model\Session',
            array('getMessages', 'getEscapeMessages'),
            array(),
            '',
            false
        );
        $backendSession->expects(
            $this->any()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue($this->getMock('Magento\Framework\Message\Collection', array(), array(), '', false))
        );

        $inlineMock = $this->getMock('Magento\Framework\Translate\Inline', array(), array(), '', false);
        $aclFilterMock = $this->getMock('Magento\Backend\Model\Layout\Filter\Acl', array(), array(), '', false);

        return array(
            array(
                'Magento\Core\Model\Resource\Theme\CollectionFactory',
                $this->getThemeCollectionFactory($countCustomization)
            ),
            array('Magento\Framework\TranslateInterface', $translate),
            array('Magento\Framework\App\Config\ScopeConfigInterface', $configMock),
            array('Magento\Framework\Event\ManagerInterface', $eventManager),
            array('Magento\Store\Model\StoreManager', $storeManager),
            array('Magento\Framework\AuthorizationInterface', $authMock),
            array('Magento\Backend\Model\Session', $backendSession),
            array('Magento\Framework\Translate\Inline', $inlineMock),
            array('Magento\Backend\Model\Layout\Filter\Acl', $aclFilterMock)
        );
    }
}
