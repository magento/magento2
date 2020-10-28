<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Template;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Edit;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    protected $editController;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Attribute|MockObject
     */
    protected $eavAttribute;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var Page|MockObject
     */
    protected $resultPage;

    /**
     * @var  Layout|MockObject
     */
    protected $resultLayout;

    /**
     * @var Config|MockObject
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Layout|MockObject
     */
    protected $layout;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var Presentation|MockObject
     */
    protected $presentation;

    /**
     * @var Title|MockObject
     */
    protected $pageTitle;

    /**
     * @var Template|MockObject
     */
    protected $blockTemplate;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactory;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();

        $this->eavAttribute = $this->createPartialMock(
            Attribute::class,
            ['setEntityTypeId', 'load', 'getId', 'getEntityTypeId', 'addData', 'getName']
        );

        $this->registry = $this->createMock(Registry::class);

        $this->resultPage = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb', 'addHandle', 'getLayout'])
            ->getMock();

        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultLayout = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageTitle = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getBlock']);

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->presentation = $this->getMockBuilder(
            Presentation::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->blockTemplate = $this->getMockBuilder(Template::class)
            ->setMethods(['setIsPopup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(Context::class)
            ->addMethods(['getResultPageFactory'])
            ->onlyMethods(['getRequest', 'getObjectManager', 'getSession'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->context->expects($this->any())->method('getResultPageFactory')->willReturn($this->resultPageFactory);
        $this->context->expects($this->any())->method('getSession')->willReturn($this->session);

        $objectManager = new ObjectManager($this);
        $this->editController = $objectManager->getObject(
            Edit::class,
            [
                'context' => $this->context,
                'resultPageFactory' => $this->resultPageFactory
            ]
        );
    }

    public function testExecutePopup()
    {
        $attributesData = ['frontend_label' => ''];

        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['attribute_id', null, null],
                ['attribute', null, $attributesData],
                ['popup', null, '1'],
                ['product_tab', null, null]
            ]
        );

        $this->objectManagerMock->expects($this->any())->method('create')
            ->with(Attribute::class)
            ->willReturn($this->eavAttribute);
        $this->objectManagerMock->expects($this->any())->method('get')
            ->willReturnMap([
                [Session::class, $this->session],
                [Presentation::class, $this->presentation]
            ]);
        $this->eavAttribute->expects($this->once())->method('setEntityTypeId')->willReturnSelf();
        $this->eavAttribute->expects($this->once())->method('addData')->with($attributesData)->willReturnSelf();
        $this->eavAttribute->expects($this->any())->method('getName')->willReturn(null);

        $this->registry->expects($this->any())
            ->method('register')
            ->with('entity_attribute', $this->eavAttribute);

        $this->resultPage->expects($this->once())
            ->method('addHandle')
            ->with(['popup', 'catalog_product_attribute_edit_popup'])
            ->willReturnSelf();
        $this->resultPage->expects($this->any())->method('getConfig')->willReturn($this->pageConfig);
        $this->resultPage->expects($this->once())->method('getLayout')->willReturn($this->layout);

        $this->resultPageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->pageConfig->expects($this->any())->method('addBodyClass')->willReturnSelf();
        $this->pageConfig->expects($this->any())->method('getTitle')->willReturn($this->pageTitle);

        $this->pageTitle->expects($this->any())->method('prepend')->willReturnSelf();

        $this->layout->expects($this->once())->method('getBlock')->willReturn($this->blockTemplate);

        $this->blockTemplate->expects($this->any())->method('setIsPopup')->willReturnSelf();

        $this->assertSame($this->resultPage, $this->editController->execute());
    }

    public function testExecuteNoPopup()
    {
        $attributesData = ['frontend_label' => ''];

        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['attribute_id', null, null],
                ['attribute', null, $attributesData],
                ['popup', null, false],
            ]
        );

        $this->objectManagerMock->expects($this->any())->method('create')
            ->with(Attribute::class)
            ->willReturn($this->eavAttribute);
        $this->objectManagerMock->expects($this->any())->method('get')
            ->willReturnMap([
                [Session::class, $this->session],
                [Presentation::class, $this->presentation]
            ]);

        $this->eavAttribute->expects($this->once())->method('setEntityTypeId')->willReturnSelf();
        $this->eavAttribute->expects($this->once())->method('addData')->with($attributesData)->willReturnSelf();

        $this->registry->expects($this->any())
            ->method('register')
            ->with('entity_attribute', $this->eavAttribute);

        $this->resultPage->expects($this->any())->method('addBreadcrumb')->willReturnSelf();
        $this->resultPage->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Catalog::catalog_attributes_attributes')
            ->willReturnSelf();
        $this->resultPage->expects($this->any())->method('getConfig')->willReturn($this->pageConfig);
        $this->resultPage->expects($this->once())->method('getLayout')->willReturn($this->layout);

        $this->resultPageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->pageConfig->expects($this->any())->method('getTitle')->willReturn($this->pageTitle);

        $this->pageTitle->expects($this->any())->method('prepend')->willReturnSelf();

        $this->eavAttribute->expects($this->any())->method('getName')->willReturn(null);

        $this->layout->expects($this->once())->method('getBlock')->willReturn($this->blockTemplate);

        $this->blockTemplate->expects($this->any())->method('setIsPopup')->willReturnSelf();

        $this->assertSame($this->resultPage, $this->editController->execute());
    }
}
