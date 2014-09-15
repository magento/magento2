<?php
/**
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

namespace Magento\GoogleShopping\Model;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerInterface;

    /** @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\GoogleShopping\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $googleShoppingHelper;

    /** @var \Magento\GoogleShopping\Helper\Category|\PHPUnit_Framework_MockObject_MockObject */
    protected $googleShoppingCategoryHelper;

    protected function setUp()
    {
        $this->collectionFactory = $this->getMockBuilder(
            'Magento\GoogleShopping\Model\Resource\Item\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(array('create'))->getMock();

        $this->itemFactory = $this->getMock('Magento\GoogleShopping\Model\ItemFactory');
        $this->productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory');
        $this->notificationInterface = $this->getMock('Magento\Framework\Notification\NotifierInterface');
        $this->storeManagerInterface = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->logger = $this->getMock('Magento\Framework\Logger', [], [], '', false);
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
            ->disableOriginalConstructor()->setMethods(array('count', 'addFieldToFilter', 'getIterator'))->getMock();
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

        $this->massOperations->synchronizeItems(array(1));
    }

    public function testSynchronizeItemsWithException()
    {
        $collection = $this->getMockBuilder('Magento\GoogleShopping\Model\Resource\Item\Collection')
            ->disableOriginalConstructor()->setMethods(array('count', 'addFieldToFilter', 'getIterator'))->getMock();
        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('count')->will($this->returnValue(1));

        $item = $this->getMockBuilder('Magento\GoogleShopping\Model\Item')->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('save')->will($this->throwException(new \Exception('Test exception')));

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(array('getName', '__sleep', '__wakeup'))->getMock();
        $product->expects($this->once())->method('getName')->will($this->returnValue('Product Name'));

        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $iterator = new \ArrayIterator([$item]);
        $collection->expects($this->once())->method('getIterator')->will($this->returnValue($iterator));

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));

        $this->notificationInterface->expects($this->once())->method('addMajor')
            ->with(
                'Errors happened during synchronization with Google Shopping',
                array('We cannot update 1 items.', 'The item "Product Name" hasn\'t been updated.')
            )->will($this->returnSelf());
        $this->massOperations->synchronizeItems(array(1));
    }

    public function testDeleteItems()
    {
        $item = $this->getMockBuilder('Magento\GoogleShopping\Model\Item')->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('deleteItem')->will($this->returnSelf());
        $item->expects($this->once())->method('delete')->will($this->returnSelf());

        $collection = $this->getMockBuilder('Magento\GoogleShopping\Model\Resource\Item\Collection')
            ->disableOriginalConstructor()->setMethods(array('count', 'addFieldToFilter', 'getIterator'))->getMock();
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

        $this->massOperations->deleteItems(array(1));
    }

    public function testDeleteItemsWitException()
    {
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(array('getName', '__sleep', '__wakeup'))->getMock();
        $product->expects($this->once())->method('getName')->will($this->returnValue('Product Name'));

        $item = $this->getMockBuilder('Magento\GoogleShopping\Model\Item')->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $item->expects($this->once())->method('deleteItem')
            ->will($this->throwException(new \Exception('Test exception')));

        $collection = $this->getMockBuilder('Magento\GoogleShopping\Model\Resource\Item\Collection')
            ->disableOriginalConstructor()->setMethods(array('count', 'addFieldToFilter', 'getIterator'))->getMock();
        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('count')->will($this->returnValue(1));
        $collection->expects($this->once())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$item])));

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));

        $this->notificationInterface->expects($this->once())->method('addMajor')
            ->with(
                'Errors happened while deleting items from Google Shopping',
                array('The item "Product Name" hasn\'t been deleted.')
            )->will($this->returnSelf());
        $this->massOperations->deleteItems(array(1));

    }
}
