<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Action\Attribute;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save */
    private $object;

    /** @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeHelper;

    /** @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $resultRedirectFactory;

    /** @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject */
    private $filter;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $collectionFactory;

    /** @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject */
    private $resultPage;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    protected function setUp()
    {
        $this->attributeHelper = $this->getMockBuilder('Magento\Catalog\Helper\Product\Edit\Action\Attribute')
            ->setMethods(['getProductIds', 'setProductIds'])
            ->disableOriginalConstructor()->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->filter = $this->getMockBuilder('Magento\Ui\Component\MassAction\Filter')
            ->setMethods(['getCollection'])->disableOriginalConstructor()->getMock();

        $this->collectionFactory = $this->getMockBuilder(
            'Magento\Catalog\Model\ResourceModel\Product\CollectionFactory'
        )->setMethods(['create'])->disableOriginalConstructor()->getMock();

        $this->resultPage = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->setMethods(['getConfig'])->disableOriginalConstructor()->getMock();

        $resultPageFactory = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $resultPageFactory->expects($this->any())->method('create')->willReturn($this->resultPage);

        $this->prepareContext();

        $this->object = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Edit',
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
        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->setMethods(['getParam', 'getParams', 'setParams'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['isProductsHasSku'])
            ->disableOriginalConstructor()->getMock();
        $product->expects($this->any())->method('isProductsHasSku')
            ->with([1, 2, 3])
            ->willReturn(true);
        $objectManager->expects($this->any())->method('create')
            ->with('Magento\Catalog\Model\Product')
            ->willReturn($product);
        $messageManager = $this->getMockBuilder('\Magento\Framework\Message\ManagerInterface')
            ->setMethods([])
            ->disableOriginalConstructor()->getMock();
        $messageManager->expects($this->any())->method('addError')->willReturn(true);
        $this->context = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->setMethods(['getRequest', 'getObjectManager', 'getMessageManager', 'getResultRedirectFactory'])
            ->disableOriginalConstructor()->getMock();
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

        $collection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->setMethods(['getAllIds'])
            ->disableOriginalConstructor()->getMock();
        $collection->expects($this->any())->method('getAllIds')->willReturn([1, 2, 3]);
        $this->filter->expects($this->any())->method('getCollection')->with($collection)->willReturn($collection);
        $this->collectionFactory->expects($this->any())->method('create')->willReturn($collection);

        $title = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->setMethods(['prepend'])
            ->disableOriginalConstructor()->getMock();
        $config = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()->getMock();
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

        $title = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->setMethods(['prepend'])
            ->disableOriginalConstructor()->getMock();
        $config = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()->getMock();
        $config->expects($this->any())->method('getTitle')->willReturn($title);
        $this->resultPage->expects($this->any())->method('getConfig')->willReturn($config);

        $this->assertSame($this->resultPage, $this->object->execute());
    }

    public function testExecutePageDirectAccess()
    {
        $this->request->expects($this->any())->method('getParam')->with('filters')->willReturn(null);
        $this->request->expects($this->any())->method('getParams')->willReturn([]);
        $this->attributeHelper->expects($this->any())->method('getProductIds')->willReturn(null);

        $resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->setMethods(['setPath'])
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
