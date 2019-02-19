<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Attribute\Backend\Consumer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Model\Attribute\Backend\Consumer */
    protected $object;

    /** @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectHelperMock;

    /** @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockIndexerProcessor;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
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

    /** @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $formKeyValidator;

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

    /**
     * @var  \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    protected function setUp()
    {
        $this->attributeHelper = $this->createPartialMock(
            \Magento\Catalog\Helper\Product\Edit\Action\Attribute::class,
            ['getProductIds', 'getSelectedStoreId', 'getStoreWebsiteId']
        );

        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockIndexerProcessor = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class,
            ['reindexList']
        );

        $this->object = (new ObjectManager($this))->getObject(
            \Magento\Catalog\Model\Attribute\Backend\Consumer::class,
            [
                'stockIndexerProcessor' => $this->stockIndexerProcessor,
                'dataObjectHelper' => $this->dataObjectHelperMock,
            ]
        );
    }

    public function testExecuteThatProductIdsAreObtainedFromAttributeHelper()
    {
        $this->attributeHelper->method('getProductIds')->will($this->returnValue([5]));
        $this->attributeHelper->method('getSelectedStoreId')->will($this->returnValue([1]));
        $this->attributeHelper->method('getStoreWebsiteId')->will($this->returnValue(1));

        $this->stockConfig->expects($this->any())->method('getConfigItemOptions')->will($this->returnValue([]));
        $this->dataObjectHelperMock->expects($this->any())
            ->method('populateWithArray')
            ->with($this->stockItem, $this->anything(), \Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->willReturnSelf();
        $this->product->expects($this->any())->method('isProductsHasSku')->with([5])->will($this->returnValue(true));
        $this->stockItemService->expects($this->any())->method('getStockItem')->with(5, 1)
            ->will($this->returnValue($this->stockItem));
        $this->stockIndexerProcessor->expects($this->any())->method('reindexList')->with([5]);

        $this->request->expects($this->any())->method('getParam')->will($this->returnValueMap([
            ['inventory', [], [7]],
        ]));

        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $this->messageManager->expects($this->never())->method('addExceptionMessage');

        $this->object->process();
    }
}
