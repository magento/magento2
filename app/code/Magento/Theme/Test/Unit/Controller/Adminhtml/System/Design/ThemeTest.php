<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var \Magento\Theme\Controller\Adminhtml\System\Design\Theme
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultFactory;

    /** @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject */
    protected $assetRepo;

    /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $appFileSystem;

    /** @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileFactory;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlag;

    /** @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendHelper;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $coreRegistry;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->_request = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->eventManager = $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false);
        $this->view = $this->getMock(\Magento\Framework\App\ViewInterface::class, [], [], '', false);
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false
        );
        $this->resultFactory = $this->getMock(\Magento\Framework\Controller\ResultFactory::class, [], [], '', false);
        $this->assetRepo = $this->getMock(\Magento\Framework\View\Asset\Repository::class, [], [], '', false);
        $this->appFileSystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->fileFactory = $this->getMock(\Magento\Framework\App\Response\Http\FileFactory::class, [], [], '', false);
        $this->response = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->redirect = $this->getMockForAbstractClass(
            \Magento\Framework\App\Response\RedirectInterface::class,
            [],
            '',
            false
        );
        $this->session = $this->getMock(
            \Magento\Backend\Model\Session::class,
            ['setIsUrlNotice', 'setThemeData', 'setThemeCustomCssData'],
            [],
            '',
            false
        );
        $this->actionFlag = $this->getMock(\Magento\Framework\App\ActionFlag::class, [], [], '', false);
        $this->backendHelper = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
        $this->coreRegistry = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Theme\Controller\Adminhtml\System\Design\Theme\\' . $this->name,
            [
                'request' => $this->_request,
                'objectManager' => $this->_objectManagerMock,
                'response' => $this->response,
                'eventManager' => $this->eventManager,
                'view' => $this->view,
                'messageManager' => $this->messageManager,
                'resultFactory' => $this->resultFactory,
                'assetRepo' => $this->assetRepo,
                'appFileSystem' => $this->appFileSystem,
                'fileFactory' => $this->fileFactory,
                'redirect' => $this->redirect,
                'session' => $this->session,
                'actionFlag' => $this->actionFlag,
                'helper' => $this->backendHelper,
                'coreRegistry' => $this->coreRegistry
            ]
        );
    }
}
