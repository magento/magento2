<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->attributeHelper = $this->getMockBuilder(Attribute::class)
            ->onlyMethods(['getProductIds', 'setProductIds'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->filter = $this->getMockBuilder(Filter::class)
            ->onlyMethods(['getCollection'])->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory = $this->getMockBuilder(
            CollectionFactory::class
        )->onlyMethods(['create'])->disableOriginalConstructor()
            ->getMock();

        $this->resultPage = $this->getMockBuilder(Page::class)
            ->onlyMethods(['getConfig'])->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->onlyMethods(['create'])->disableOriginalConstructor()
            ->getMock();
        $resultPageFactory->expects($this->any())->method('create')->willReturn($this->resultPage);

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
        $this->request = $this->getMockBuilder(Http::class)
            ->onlyMethods(['getParam', 'getParams', 'setParams'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['isProductsHasSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('isProductsHasSku')
            ->with([1, 2, 3])
            ->willReturn(true);
        $objectManager->expects($this->any())
            ->method('create')
            ->with(Product::class)
            ->willReturn($product);
        $messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $messageManager->expects($this->any())->method('addErrorMessage')->willReturn(true);
        $this->context = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getRequest', 'getObjectManager', 'getMessageManager', 'getResultRedirectFactory'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($objectManager);
        $this->context->expects($this->any())->method('getMessageManager')->willReturn($messageManager);
        $this->context->expects($this->any())->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);
    }

    public function testExecutePageRequested()
    {
        $this->request->expects($this->any())->method('getParam')->with('filters')->willReturn(['placeholder' => true]);
        $this->request->expects($this->any())->method('getParams')->willReturn(
            [
                'namespace' => 'product_listing',
                'exclude' => true,
                'filters' => ['placeholder' => true]
            ]
        );

        $this->attributeHelper->expects($this->any())->method('getProductIds')->willReturn([1, 2, 3]);
        $this->attributeHelper->expects($this->any())->method('setProductIds')->with([1, 2, 3]);

        $collection = $this->getMockBuilder(Collection::class)
            ->onlyMethods(['getAllIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())->method('getAllIds')->willReturn([1, 2, 3]);
        $this->filter->expects($this->any())->method('getCollection')->with($collection)->willReturn($collection);
        $this->collectionFactory->expects($this->any())->method('create')->willReturn($collection);

        $title = $this->getMockBuilder(Title::class)
            ->onlyMethods(['prepend'])
            ->disableOriginalConstructor()
            ->getMock();
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->any())->method('getTitle')->willReturn($title);
        $this->resultPage->expects($this->any())->method('getConfig')->willReturn($config);

        $this->assertSame($this->resultPage, $this->object->execute());
    }

    public function testExecutePageReload()
    {
        $this->request->expects($this->any())->method('getParam')->with('filters')->willReturn(null);
        $this->request->expects($this->any())->method('getParams')->willReturn([]);

        $this->attributeHelper->expects($this->any())->method('getProductIds')->willReturn([1, 2, 3]);
        $this->attributeHelper->expects($this->any())->method('setProductIds')->with([1, 2, 3]);

        $title = $this->getMockBuilder(Title::class)
            ->onlyMethods(['prepend'])
            ->disableOriginalConstructor()
            ->getMock();
        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->any())->method('getTitle')->willReturn($title);
        $this->resultPage->expects($this->any())->method('getConfig')->willReturn($config);

        $this->assertSame($this->resultPage, $this->object->execute());
    }

    public function testExecutePageDirectAccess()
    {
        $this->request->expects($this->any())->method('getParam')->with('filters')->willReturn(null);
        $this->request->expects($this->any())->method('getParams')->willReturn([]);
        $this->attributeHelper->expects($this->any())->method('getProductIds')->willReturn(null);

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->onlyMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->any())->method('setPath')
            ->with('catalog/product/', ['_current' => true])
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->assertSame($resultRedirect, $this->object->execute());
    }
}
