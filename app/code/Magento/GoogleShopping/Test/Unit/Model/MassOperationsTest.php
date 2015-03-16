<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class MassOperationsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\GoogleShopping\Model\MassOperations */
    protected $massOperations;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $collectionFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $itemFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $productRepository;

    /** @var \Magento\Framework\Notification\NotifierInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $notificationInterface;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerInterface;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\GoogleShopping\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $googleShoppingHelper;

    /** @var \Magento\GoogleShopping\Helper\Category|\PHPUnit_Framework_MockObject_MockObject */
    protected $googleShoppingCategoryHelper;

    /** @var \Magento\GoogleShopping\Model\Flag|\PHPUnit_Framework_MockObject_MockObject */
    protected $flag;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->collectionFactory = $this->getMockBuilder(
            'Magento\GoogleShopping\Model\Resource\Item\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->itemFactory = $this->getMock('Magento\GoogleShopping\Model\ItemFactory', ['create'], [], '', false);
        $this->productRepository = $this->getMock(
            '\Magento\Catalog\Api\ProductRepositoryInterface',
            ['save', 'get', 'delete', 'getById', 'deleteById', 'getList'],
            [],
            '',
            false
        );
        $this->notificationInterface = $this->getMock('Magento\Framework\Notification\NotifierInterface');
        $this->storeManagerInterface = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->googleShoppingHelper = $this->getMock('Magento\GoogleShopping\Helper\Data', [], [], '', false);
        $this->googleShoppingCategoryHelper = $this->getMock('Magento\GoogleShopping\Helper\Category');
        $this->flag = $this->getMock('Magento\GoogleShopping\Model\Flag', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->massOperations = $this->objectManagerHelper->getObject(
            'Magento\GoogleShopping\Model\MassOperations',
            [
                'collectionFactory' => $this->collectionFactory,
                'itemFactory' => $this->itemFactory,
                'productRepository' => $this->productRepository,
                'notifier' => $this->notificationInterface,
                'storeManager' => $this->storeManagerInterface,
                'logger' => $this->logger,
                'gleShoppingData' => $this->googleShoppingHelper,
                'gleShoppingCategory' => $this->googleShoppingCategoryHelper
            ]
        );
    }

    /**
     * @return void
     */
    public function testAddProducts()
    {
        $products = ['1','2'];
        $product = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepository->expects($this->exactly(2))->method('getById')->will($this->returnValue($product));
        $googleShoppingItem = $this->getMock('\Magento\GoogleShopping\Model\Item', [], [], '', false);
        $googleShoppingItem->expects($this->exactly(2))->method('insertItem')->will($this->returnSelf());
        $this->itemFactory->expects($this->exactly(2))->method('create')->will($this->returnValue($googleShoppingItem));
        $this->flag->expects($this->any())->method('isExpired')->will($this->returnValue(false));
        $this->massOperations->setFlag($this->flag);
        $this->assertEquals($this->massOperations->addProducts($products, 1), $this->massOperations);
    }

    /**
     * @return void
     */
    public function testAddProductsExpiredFlag()
    {
        $products = ['1','2'];
        $this->flag->expects($this->exactly(2))->method('isExpired')->will($this->returnValue(true));
        $this->massOperations->setFlag($this->flag);
        $this->massOperations->addProducts($products, 1);
    }

    /**
     * @param string $exception
     * @return void
     * @dataProvider dataAddProductsExceptions
     */
    public function testAddProductsExceptions($exception)
    {
        $products = ['1'];
        $this->flag->expects($this->any())->method('isExpired')->will($this->returnValue(false));
        $product = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepository->expects($this->once())->method('getById')->will($this->returnValue($product));
        $this->itemFactory->expects($this->once())
            ->method('create')
            ->willThrowException(new $exception(__('message')));
        $this->massOperations->setFlag($this->flag);
        $this->massOperations->addProducts($products, 1);
    }

    /**
     * @return array
     */
    public function dataAddProductsExceptions()
    {
        return [
            ['\Magento\Framework\Exception\NoSuchEntityException'],
            ['\Zend_Gdata_App_Exception'],
            ['\Zend_Db_Statement_Exception'],
            ['\Magento\Framework\Exception\LocalizedException'],
            ['\Exception']
        ];
    }

    /**
     * @return void
     */
    public function testSynchronizeItems()
    {
        $collection = $this->getMockBuilder('Magento\GoogleShopping\Model\Resource\Item\Collection')
            ->disableOriginalConstructor()->setMethods(['count', 'addFieldToFilter', 'getIterator'])->getMock();
        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('count')->will($this->returnValue(1));

        $item = $this->getMockBuilder('Magento\GoogleShopping\Model\Item')->disableOriginalConstructor()->getMock();
        $iterator = new \ArrayIterator([$item]);
        $collection->expects($this->once())->method('getIterator')->will($this->returnValue($iterator));

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));

        $this->notificationInterface->expects($this->once())->method('addNotice')
            ->with(
                'Product synchronization with Google Shopping completed',
                'A total of 0 items(s) have been deleted; a total of 1 items(s) have been updated.'
            )->will($this->returnSelf());

        $this->massOperations->synchronizeItems([1]);
    }

    /**
     * @return void
     */
    public function testSynchronizeItemsWithException()
    {
        $collection = $this->getMockBuilder('Magento\GoogleShopping\Model\Resource\Item\Collection')
            ->disableOriginalConstructor()->setMethods(['count', 'addFieldToFilter', 'getIterator'])->getMock();
        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('count')->will($this->returnValue(1));

        $item = $this->getMockBuilder('Magento\GoogleShopping\Model\Item')->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('save')->will($this->throwException(new \Exception('Test exception')));

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(['getName', '__sleep', '__wakeup'])->getMock();
        $product->expects($this->once())->method('getName')->will($this->returnValue('Product Name'));

        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $iterator = new \ArrayIterator([$item]);
        $collection->expects($this->once())->method('getIterator')->will($this->returnValue($iterator));

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));

        $this->notificationInterface->expects($this->once())->method('addMajor')
            ->with(
                'Errors happened during synchronization with Google Shopping',
                ['We cannot update 1 items.', 'The item "Product Name" hasn\'t been updated.']
            )->will($this->returnSelf());
        $this->massOperations->synchronizeItems([1]);
    }

    /**
     * @return void
     */
    public function testDeleteItems()
    {
        $item = $this->getMockBuilder('Magento\GoogleShopping\Model\Item')->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('deleteItem')->will($this->returnSelf());
        $item->expects($this->once())->method('delete')->will($this->returnSelf());

        $collection = $this->getMockBuilder('Magento\GoogleShopping\Model\Resource\Item\Collection')
            ->disableOriginalConstructor()->setMethods(['count', 'addFieldToFilter', 'getIterator'])->getMock();
        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('count')->will($this->returnValue(1));
        $collection->expects($this->once())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$item])));

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));

        $this->notificationInterface->expects($this->once())->method('addNotice')
            ->with(
                'Google Shopping item removal process succeded',
                'Total of 1 items(s) have been removed from Google Shopping.'
            )->will($this->returnSelf());

        $this->massOperations->deleteItems([1]);
    }

    /**
     * @return void
     */
    public function testDeleteItemsWitException()
    {
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(['getName', '__sleep', '__wakeup'])->getMock();
        $product->expects($this->once())->method('getName')->will($this->returnValue('Product Name'));

        $item = $this->getMockBuilder('Magento\GoogleShopping\Model\Item')->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $item->expects($this->once())->method('deleteItem')
            ->will($this->throwException(new \Exception('Test exception')));

        $collection = $this->getMockBuilder('Magento\GoogleShopping\Model\Resource\Item\Collection')
            ->disableOriginalConstructor()->setMethods(['count', 'addFieldToFilter', 'getIterator'])->getMock();
        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('count')->will($this->returnValue(1));
        $collection->expects($this->once())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$item])));

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));

        $this->notificationInterface->expects($this->once())->method('addMajor')
            ->with(
                'Errors happened while deleting items from Google Shopping',
                ['The item "Product Name" hasn\'t been deleted.']
            )->will($this->returnSelf());
        $this->massOperations->deleteItems([1]);
    }
}
