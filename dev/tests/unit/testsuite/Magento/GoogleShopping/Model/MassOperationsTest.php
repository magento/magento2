<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
    protected $productFactory;

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

    protected function setUp()
    {
        $this->collectionFactory = $this->getMockBuilder(
            'Magento\GoogleShopping\Model\Resource\Item\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->itemFactory = $this->getMock('Magento\GoogleShopping\Model\ItemFactory', [], [], '', false);
        $this->productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory', [], [], '', false);
        $this->notificationInterface = $this->getMock('Magento\Framework\Notification\NotifierInterface');
        $this->storeManagerInterface = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->googleShoppingHelper = $this->getMock('Magento\GoogleShopping\Helper\Data', [], [], '', false);
        $this->googleShoppingCategoryHelper = $this->getMock('Magento\GoogleShopping\Helper\Category');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->massOperations = $this->objectManagerHelper->getObject(
            'Magento\GoogleShopping\Model\MassOperations',
            [
                'collectionFactory' => $this->collectionFactory,
                'itemFactory' => $this->itemFactory,
                'productFactory' => $this->productFactory,
                'notifier' => $this->notificationInterface,
                'storeManager' => $this->storeManagerInterface,
                'logger' => $this->logger,
                'gleShoppingData' => $this->googleShoppingHelper,
                'gleShoppingCategory' => $this->googleShoppingCategoryHelper
            ]
        );
    }

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
