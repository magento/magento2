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
 * @package     Magento_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test backend controller for the design editor
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design;

class EditorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');

        $request = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $request->expects($this->any())->method('setActionName')->will($this->returnSelf());

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        /** @var $layoutMock \Magento\Core\Model\Layout|PHPUnit_Framework_MockObject_MockObject */
        $layoutMock = $this->getMock('Magento\Core\Model\Layout',
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
            array(), '', false);
        /** @var $layoutMock \Magento\Core\Model\Layout */
        $layoutMock->expects($this->any())->method('generateXml')->will($this->returnSelf());
        $layoutMock->expects($this->any())->method('getNode')
            ->will($this->returnValue(new \Magento\Simplexml\Element('<root />')));
        $blockMessage = $this->getMock('Magento\Core\Block\Messages',
            array('addMessages', 'setEscapeMessageFlag', 'addStorageType'), array(), '', false);
        $layoutMock->expects($this->any())->method('getMessagesBlock')->will($this->returnValue($blockMessage));

        $blockMock = $this->getMock('Magento\Core\Block\Template', array('setActive', 'getMenuModel', 'getParentItems'),
            array(), '', false);
        $blockMock->expects($this->any())->method('getMenuModel')->will($this->returnSelf());
        $blockMock->expects($this->any())->method('getParentItems')->will($this->returnValue(array()));

        $layoutMock->expects($this->any())->method('getBlock')->will($this->returnValue($blockMock));
        $layoutMock->expects($this->any())->method('getUpdate')->will($this->returnSelf());

        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor',
            array(
                'request' => $request,
                'objectManager' => $this->_objectManagerMock,
                'layout' => $layoutMock,
                'invokeArgs' => array(
                    'helper' => $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false),
                    'session'=> $this->getMock('Magento\Backend\Model\Session', array(), array(), '', false),
            ))
        );

        $this->_model = $objectManagerHelper
            ->getObject('Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor', $constructArguments);
    }

    /**
     * Return mocked theme collection factory model
     *
     * @param int $countCustomization
     * @return \Magento\Core\Model\Resource\Theme\CollectionFactory
     */
    protected function _getThemeCollectionFactory($countCustomization)
    {
        $themeCollectionMock = $this->getMockBuilder('Magento\Core\Model\Resource\Theme\Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('addTypeFilter', 'getSize'))
            ->getMock();

        $themeCollectionMock->expects($this->once())
            ->method('addTypeFilter')
            ->with(\Magento\Core\Model\Theme::TYPE_VIRTUAL)
            ->will($this->returnValue($themeCollectionMock));

        $themeCollectionMock->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($countCustomization));

        /** @var \Magento\Core\Model\Resource\Theme\CollectionFactory $collectionFactory */
        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory', array('create'), array(), '', false
        );
        $collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($themeCollectionMock));

        return $collectionFactory;
    }

    /**
     * @covers \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor::indexAction
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($countCustomization)
    {
        $this->_objectManagerMock->expects($this->any())->method('get')
            ->will($this->returnValueMap($this->_getObjectManagerMap($countCustomization, 'index')));
        $this->assertNull($this->_model->indexAction());
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        return array(
            array(4),
            array(0)
        );
    }

    /**
     * @covers \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor::firstEntranceAction
     * @dataProvider firstEntranceActionDataProvider
     */
    public function testFirstEntranceAction($countCustomization)
    {
        $this->_objectManagerMock->expects($this->any())->method('get')
            ->will($this->returnValueMap($this->_getObjectManagerMap($countCustomization)));
        $this->assertNull($this->_model->firstEntranceAction());
    }

    /**
     * @return array
     */
    public function firstEntranceActionDataProvider()
    {
        return array(
            array(3),
            array(0)
        );
    }

    /**
     * @param int $countCustomization
     * @return array
     */
    protected function _getObjectManagerMap($countCustomization)
    {
        $translate = $this->getMock('Magento\Core\Model\Translate', array(), array(), '', false);
        $translate->expects($this->any())->method('translate')
            ->will($this->returnSelf());

        $storeManager = $this->getMock('Magento\Core\Model\StoreManager',
            array('getStore', 'getBaseUrl'), array(), '', false);
        $storeManager->expects($this->any())->method('getStore')
            ->will($this->returnSelf());

        $eventManager = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        $configMock = $this->getMock('Magento\Core\Model\Config', array(), array(), '', false);
        $authMock = $this->getMock('Magento\AuthorizationInterface');
        $authMock->expects($this->any())->method('filterAclNodes')->will($this->returnSelf());
        $backendSession = $this->getMock('Magento\Backend\Model\Session', array('getMessages', 'getEscapeMessages'),
            array(), '', false);
        $backendSession->expects($this->any())->method('getMessages')->will(
            $this->returnValue($this->getMock('Magento\Core\Model\Message\Collection', array(), array(), '', false))
        );

        $inlineMock = $this->getMock('Magento\Core\Model\Translate\Inline', array(), array(), '', false);
        $aclFilterMock = $this->getMock('Magento\Core\Model\Layout\Filter\Acl', array(), array(), '', false);

        return array(
            array(
                'Magento\Core\Model\Resource\Theme\CollectionFactory',
                $this->_getThemeCollectionFactory($countCustomization)
            ),
            array('Magento\Core\Model\Translate', $translate),
            array('Magento\Core\Model\Config', $configMock),
            array('Magento\Event\ManagerInterface', $eventManager),
            array('Magento\Core\Model\StoreManager', $storeManager),
            array('Magento\AuthorizationInterface', $authMock),
            array('Magento\Backend\Model\Session', $backendSession),
            array('Magento\Core\Model\Translate\Inline', $inlineMock),
            array('Magento\Core\Model\Layout\Filter\Acl', $aclFilterMock),
        );
    }
}
