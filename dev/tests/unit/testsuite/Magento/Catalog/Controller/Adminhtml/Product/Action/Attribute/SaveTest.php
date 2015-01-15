<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save */
    protected $object;

    /** @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemBuilder;

    /** @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockIndexerProcessor;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManager;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManager;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $url;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlag;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $view;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authorization;

    /** @var \Magento\Backend\Model\Auth|\PHPUnit_Framework_MockObject_MockObject */
    protected $auth;

    /** @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;

    /** @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendUrl;

    /** @var \Magento\Core\App\Action\FormKeyValidator|\PHPUnit_Framework_MockObject_MockObject */
    protected $formKeyValidator;

    /** @var \Magento\Framework\App\Action\Title|\PHPUnit_Framework_MockObject_MockObject */
    protected $title;

    /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeResolver;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /** @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemService;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItem;

    /** @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemRepository;

    protected function setUp()
    {
        $this->prepareContext();

        $this->attributeHelper = $this->getMock(
            'Magento\Catalog\Helper\Product\Edit\Action\Attribute',
            ['getProductIds', 'getSelectedStoreId', 'getStoreWebsiteId'],
            [],
            '',
            false
        );

        $this->stockItemBuilder = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['mergeDataObjectWithArray', 'create'])
            ->getMock();

        $this->stockIndexerProcessor = $this->getMock(
            'Magento\CatalogInventory\Model\Indexer\Stock\Processor',
            ['reindexList'],
            [],
            '',
            false
        );

        $resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->object = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save',
            [
                'context' => $this->context,
                'attributeHelper' => $this->attributeHelper,
                'stockIndexerProcessor' => $this->stockIndexerProcessor,
                'stockItemBuilder' => $this->stockItemBuilder,
                'resultRedirectFactory' => $resultRedirectFactory
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function prepareContext()
    {
        $this->stockItemRepository = $this->getMockBuilder('Magento\CatalogInventory\Api\StockItemRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['getParam', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getCookie'],
            [],
            '',
            false
        );
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->url = $this->getMock('Magento\Framework\UrlInterface', [], [], '', false);
        $this->redirect = $this->getMock('Magento\Framework\App\Response\RedirectInterface', [], [], '', false);
        $this->actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $this->view = $this->getMock('Magento\Framework\App\ViewInterface', [], [], '', false);
        $this->messageManager = $this->getMock('Magento\Framework\Message\ManagerInterface', [], [], '', false);
        $this->session = $this->getMock('Magento\Backend\Model\Session', [], [], '', false);
        $this->authorization = $this->getMock('Magento\Framework\AuthorizationInterface', [], [], '', false);
        $this->auth = $this->getMock('Magento\Backend\Model\Auth', [], [], '', false);
        $this->helper = $this->getMock('Magento\Backend\Helper\Data', [], [], '', false);
        $this->backendUrl = $this->getMock('Magento\Backend\Model\UrlInterface', [], [], '', false);
        $this->formKeyValidator = $this->getMock('Magento\Core\App\Action\FormKeyValidator', [], [], '', false);
        $this->title = $this->getMock('Magento\Framework\App\Action\Title', [], [], '', false);
        $this->localeResolver = $this->getMock('Magento\Framework\Locale\ResolverInterface', [], [], '', false);

        $this->context = $this->context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getEventManager',
                'getUrl',
                'getRedirect',
                'getActionFlag',
                'getView',
                'getMessageManager',
                'getSession',
                'getAuthorization',
                'getAuth',
                'getHelper',
                'getBackendUrl',
                'getFormKeyValidator',
                'getTitle',
                'getLocaleResolver',
            ],
            [],
            '',
            false
        );
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->context->expects($this->any())->method('getObjectManager')
            ->will($this->returnValue($this->objectManager));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($this->eventManager));
        $this->context->expects($this->any())->method('getUrl')->will($this->returnValue($this->url));
        $this->context->expects($this->any())->method('getRedirect')->will($this->returnValue($this->redirect));
        $this->context->expects($this->any())->method('getActionFlag')->will($this->returnValue($this->actionFlag));
        $this->context->expects($this->any())->method('getView')->will($this->returnValue($this->view));
        $this->context->expects($this->any())->method('getMessageManager')
            ->will($this->returnValue($this->messageManager));
        $this->context->expects($this->any())->method('getSession')->will($this->returnValue($this->session));
        $this->context->expects($this->any())->method('getAuthorization')
            ->will($this->returnValue($this->authorization));
        $this->context->expects($this->any())->method('getAuth')->will($this->returnValue($this->auth));
        $this->context->expects($this->any())->method('getHelper')->will($this->returnValue($this->helper));
        $this->context->expects($this->any())->method('getBackendUrl')->will($this->returnValue($this->backendUrl));
        $this->context->expects($this->any())->method('getFormKeyValidator')
            ->will($this->returnValue($this->formKeyValidator));
        $this->context->expects($this->any())->method('getTitle')->will($this->returnValue($this->title));
        $this->context->expects($this->any())->method('getLocaleResolver')
            ->will($this->returnValue($this->localeResolver));

        $this->product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['isProductsHasSku', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockItemService = $this->getMockBuilder('Magento\CatalogInventory\Api\StockRegistryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', 'saveStockItem'])
            ->getMockForAbstractClass();
        $this->stockItem = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemInterface')
            ->setMethods(['getId', 'getProductId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockConfig = $this->getMockBuilder('Magento\CatalogInventory\Api\StockConfigurationInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManager->expects($this->any())->method('create')->will($this->returnValueMap([
            ['Magento\Catalog\Model\Product', [], $this->product],
            ['Magento\CatalogInventory\Api\StockRegistryInterface', [], $this->stockItemService],
            ['Magento\CatalogInventory\Api\StockItemRepositoryInterface', [], $this->stockItemRepository],
        ]));

        $this->objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            ['Magento\CatalogInventory\Api\StockConfigurationInterface', $this->stockConfig],
        ]));
    }

    public function testExecuteThatProductIdsAreObtainedFromAttributeHelper()
    {
        $this->attributeHelper->expects($this->any())->method('getProductIds')->will($this->returnValue([5]));
        $this->attributeHelper->expects($this->any())->method('getSelectedStoreId')->will($this->returnValue([1]));
        $this->attributeHelper->expects($this->any())->method('getStoreWebsiteId')->will($this->returnValue(1));
        $this->stockConfig->expects($this->any())->method('getConfigItemOptions')->will($this->returnValue([]));
        $itemToSave = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setItemId', 'save'])
            ->getMockForAbstractClass();
        $this->stockItemBuilder->expects($this->any())
            ->method('mergeDataObjectWithArray')
            ->withAnyParameters()
            ->willReturnSelf();
        $this->stockItemBuilder->expects($this->any())
            ->method('create')
            ->willReturn($itemToSave);
        $this->product->expects($this->any())->method('isProductsHasSku')->with([5])->will($this->returnValue(true));
        $this->stockItemService->expects($this->any())->method('getStockItem')->with(5, 1)
            ->will($this->returnValue($this->stockItem));
        $this->stockIndexerProcessor->expects($this->any())->method('reindexList')->with([5]);

        $this->request->expects($this->any())->method('getParam')->will($this->returnValueMap([
            ['inventory', [], [7]],
        ]));

        $this->messageManager->expects($this->never())->method('addError');
        $this->messageManager->expects($this->never())->method('addException');

        $this->object->execute();
    }
}
