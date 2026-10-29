<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Edit;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
    /** @var Save */
    private $object;

    /** @var Attribute|MockObject */
    private $attributeHelper;

    /** @var RedirectFactory|MockObject */
    private $resultRedirectFactory;

    /** @var Filter|MockObject */
    private $filter;

    /** @var Context|MockObject */
    private $context;

    /** @var CollectionFactory|MockObject */
    private $collectionFactory;

    /** @var Page|MockObject */
    private $resultPage;

    /** @var Http|MockObject */
    private $request;

    protected function setUp(): void
    {
        $this->attributeHelper = $this->createPartialMock(
            Attribute::class,
            ['getProductIds', 'setProductIds']
        );

        $this->resultRedirectFactory = $this->createPartialMock(RedirectFactory::class, ['create']);

        $this->filter = $this->createPartialMock(Filter::class, ['getCollection']);

        $this->collectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);

        $this->resultPage = $this->createPartialMock(Page::class, ['getConfig']);

        $resultPageFactory = $this->createPartialMock(PageFactory::class, ['create']);
        $resultPageFactory->method('create')->willReturn($this->resultPage);

        $this->prepareContext();

        $this->object = (new ObjectManager($this))->getObject(
            Edit::class,
            [
                'context' => $this->context,
                'attributeHelper' => $this->attributeHelper,
                'filter' => $this->filter,
                'resultPageFactory' => $resultPageFactory,
                'collectionFactory' => $this->collectionFactory
            ]
        );
    }

    private function prepareContext()
    {
        $this->request = $this->createPartialMock(Http::class, ['getParam', 'getParams', 'setParams']);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $product = $this->createPartialMock(Product::class, ['isProductsHasSku']);
        $product->expects($this->any())->method('isProductsHasSku')
            ->with([1, 2, 3])
            ->willReturn(true);
        $objectManager->expects($this->any())
            ->method('create')
            ->with(Product::class)
            ->willReturn($product);
        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->method('addErrorMessage')->willReturn(true);
        $this->context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getObjectManager', 'getMessageManager', 'getResultRedirectFactory']
        );
        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getObjectManager')->willReturn($objectManager);
        $this->context->method('getMessageManager')->willReturn($messageManager);
        $this->context->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);
    }

    public function testExecutePageRequested()
    {
        $this->request->expects($this->any())->method('getParam')->with('filters')->willReturn(['placeholder' => true]);
        $this->request->method('getParams')->willReturn(
            [
                'namespace' => 'product_listing',
                'exclude' => true,
                'filters' => ['placeholder' => true]
            ]
        );

        $this->attributeHelper->method('getProductIds')->willReturn([1, 2, 3]);
        $this->attributeHelper->expects($this->any())->method('setProductIds')->with([1, 2, 3]);

        $collection = $this->createPartialMock(Collection::class, ['getAllIds']);
        $collection->method('getAllIds')->willReturn([1, 2, 3]);
        $this->filter->expects($this->any())->method('getCollection')->with($collection)->willReturn($collection);
        $this->collectionFactory->method('create')->willReturn($collection);

        $title = $this->createPartialMock(Title::class, ['prepend']);
        $config = $this->createPartialMock(Config::class, ['getTitle']);
        $config->method('getTitle')->willReturn($title);
        $this->resultPage->method('getConfig')->willReturn($config);

        $this->assertSame($this->resultPage, $this->object->execute());
    }

    public function testExecutePageReload()
    {
        $this->request->expects($this->any())->method('getParam')->with('filters')->willReturn(null);
        $this->request->method('getParams')->willReturn([]);

        $this->attributeHelper->method('getProductIds')->willReturn([1, 2, 3]);
        $this->attributeHelper->expects($this->any())->method('setProductIds')->with([1, 2, 3]);

        $title = $this->createPartialMock(Title::class, ['prepend']);
        $config = $this->createPartialMock(Config::class, ['getTitle']);
        $config->method('getTitle')->willReturn($title);
        $this->resultPage->method('getConfig')->willReturn($config);

        $this->assertSame($this->resultPage, $this->object->execute());
    }

    public function testExecutePageDirectAccess()
    {
        $this->request->expects($this->any())->method('getParam')->with('filters')->willReturn(null);
        $this->request->method('getParams')->willReturn([]);
        $this->attributeHelper->method('getProductIds')->willReturn(null);

        $resultRedirect = $this->createPartialMock(Redirect::class, ['setPath']);
        $resultRedirect->expects($this->any())->method('setPath')
            ->with('catalog/product/', ['_current' => true])
            ->willReturnSelf();
        $this->resultRedirectFactory->method('create')->willReturn($resultRedirect);

        $this->assertSame($resultRedirect, $this->object->execute());
    }
}
