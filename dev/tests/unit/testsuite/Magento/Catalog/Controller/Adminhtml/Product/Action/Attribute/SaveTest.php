<?php
/**
 *
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
    /** @var \Magento\CatalogInventory\Service\V1\Data\StockItemBuilder|\PHPUnit_Framework_MockObject_MockObject */
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
    /** @var \Magento\CatalogInventory\Service\V1\StockItemService|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemService;
    /** @var \Magento\CatalogInventory\Service\V1\Data\StockItem|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItem;
    /** @var \Magento\CatalogInventory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $inventoryHelper;

    protected function setUp()
    {
        $this->prepareContext();

        $this->attributeHelper = $this->getMock(
            'Magento\Catalog\Helper\Product\Edit\Action\Attribute',
            ['getProductIds', 'getSelectedStoreId'],
            [],
            '',
            false
        );

        $this->stockItemBuilder = $this->getMock(
            'Magento\CatalogInventory\Service\V1\Data\StockItemBuilder',
            ['mergeDataObjectWithArray'],
            [],
            '',
            false
        );

        $this->stockIndexerProcessor = $this->getMock(
            'Magento\CatalogInventory\Model\Indexer\Stock\Processor',
            ['reindexList'],
            [],
            '',
            false
        );

        $this->object = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save',
            [
                'context' => $this->context,
                'attributeHelper' => $this->attributeHelper,
                'stockIndexerProcessor' => $this->stockIndexerProcessor,
                'stockItemBuilder' => $this->stockItemBuilder,
            ]
        );

    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function prepareContext()
    {
        $this->request = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['getParam', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getCookie'],
            [],
            '',
            false
        );
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->objectManager = $this->getMock(
            'Magento\Framework\ObjectManager',
            ['configure', 'create', 'get'],
            [],
            '',
            false
        );
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
        $this->stockItemService = $this->getMock(
            'Magento\CatalogInventory\Service\V1\StockItemService',
            ['getStockItem', 'saveStockItem'],
            [],
            '',
            false
        );
        $this->stockItem = $this->getMock('Magento\CatalogInventory\Service\V1\Data\StockItem', [], [], '', false);
        $this->inventoryHelper
            = $this->getMock('Magento\CatalogInventory\Helper\Data', ['getConfigItemOptions'], [], '', false);

        $this->objectManager->expects($this->any())->method('create')->will($this->returnValueMap([
            ['Magento\Catalog\Model\Product', [], $this->product],
            ['Magento\CatalogInventory\Service\V1\StockItemService', [], $this->stockItemService],
        ]));

        $this->objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            ['Magento\CatalogInventory\Helper\Data', $this->inventoryHelper],
        ]));
    }

    public function testExecuteThatProductIdsAreObtainedFromAttributeHelper()
    {
        $this->attributeHelper->expects($this->any())->method('getProductIds')->will($this->returnValue([5]));
        $this->attributeHelper->expects($this->any())->method('getSelectedStoreId')->will($this->returnValue([1]));
        $this->inventoryHelper->expects($this->any())->method('getConfigItemOptions')->will($this->returnValue([]));
        $this->product->expects($this->any())->method('isProductsHasSku')->with([5])->will($this->returnValue(true));
        $this->stockItemService->expects($this->any())->method('getStockItem')->with(5)
            ->will($this->returnValue($this->stockItem));
        $this->stockIndexerProcessor->expects($this->any())->method('reindexList')->with([5]);

        $this->request->expects($this->any())->method('getParam')->will($this->returnValueMap([
            ['inventory', [], [7]]
        ]));

        $this->messageManager->expects($this->never())->method('addError');
        $this->messageManager->expects($this->never())->method('addException');

        $this->object->execute();
    }
}
