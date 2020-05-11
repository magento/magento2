<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design;

use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\Repository;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ThemeTest extends TestCase
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var Theme
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var MockObject
     */
    protected $_request;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var ViewInterface|MockObject
     */
    protected $view;

    /** @var \Magento\Framework\Message\ManagerInterface|MockObject */
    protected $messageManager;

    /** @var \Magento\Framework\Message\ManagerInterface|MockObject */
    protected $resultFactory;

    /** @var Repository|MockObject */
    protected $assetRepo;

    /** @var Filesystem|MockObject */
    protected $appFileSystem;

    /** @var FileFactory|MockObject */
    protected $fileFactory;

    /** @var Http|MockObject */
    protected $response;

    /** @var RedirectInterface|MockObject */
    protected $redirect;

    /** @var Session|MockObject */
    protected $session;

    /** @var ActionFlag|MockObject */
    protected $actionFlag;

    /** @var Data|MockObject */
    protected $backendHelper;

    /** @var Registry|MockObject */
    protected $coreRegistry;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->_request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->view = $this->getMockForAbstractClass(ViewInterface::class);
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false
        );
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->assetRepo = $this->createMock(Repository::class);
        $this->appFileSystem = $this->createMock(Filesystem::class);
        $this->fileFactory = $this->createMock(FileFactory::class);
        $this->response = $this->createMock(Http::class);
        $this->redirect = $this->getMockForAbstractClass(
            RedirectInterface::class,
            [],
            '',
            false
        );
        $this->session = $this->getMockBuilder(Session::class)
            ->addMethods(['setIsUrlNotice', 'setThemeData', 'setThemeCustomCssData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlag = $this->createMock(ActionFlag::class);
        $this->backendHelper = $this->createMock(Data::class);
        $this->coreRegistry = $this->createMock(Registry::class);

        $helper = new ObjectManager($this);
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
