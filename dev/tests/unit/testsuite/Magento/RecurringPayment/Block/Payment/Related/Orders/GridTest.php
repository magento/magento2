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
namespace Magento\RecurringPayment\Block\Payment\Related\Orders;

/**
 * Test class for \Magento\RecurringPayment\Block\Payment\Related\Orders\Grid
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\RecurringPayment\Block\Payment\Related\Orders\Grid
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Collection | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $locale;

    /**
     * @var \Magento\Core\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Magento\RecurringPayment\Model\Resource\Order\CollectionFilter | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $recurringCollectionFilter;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->storeManager = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->collection = $this->getMock('Magento\Sales\Model\Resource\Order\Collection', [], [], '', false);
        $this->locale = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->helper = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $this->recurringCollectionFilter = $this->getMock(
            'Magento\RecurringPayment\Model\Resource\Order\CollectionFilter',
            ['byIds'],
            [],
            '',
            false
        );

        $this->block = $objectManagerHelper->getObject(
            'Magento\RecurringPayment\Block\Payment\Related\Orders\Grid',
            array(
                'registry' => $this->registry,
                'storeManager' => $this->storeManager,
                'collection' => $this->collection,
                'localeDate' => $this->locale,
                'coreHelper' => $this->helper,
                'recurringCollectionFilter' => $this->recurringCollectionFilter
            )
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareLayout()
    {
        $customerId = 1;
        $store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $args = array(
            'getIncrementId',
            'getCreatedAt',
            'getCustomerName',
            'getBaseGrandTotal',
            'getStatusLabel',
            'getId',
            '__wakeup'
        );
        $collectionElement = $this->getMock('Magento\RecurringPayment\Model\Payment', $args, array(), '', false);
        $collectionElement->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue(1));
        $this->collection->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue($this->collection));
        $this->collection->expects($this->once())
            ->method('addFieldToSelect')
            ->will($this->returnValue($this->collection));
        $this->collection->expects($this->once())
            ->method('setOrder')
            ->will($this->returnValue($this->collection));
        $this->collection->expects(
            $this->once()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue(new \ArrayIterator(array($collectionElement)))
        );
        $payment = $this->getMock('Magento\RecurringPayment\Model\Payment', array(), array(), '', false);
        $this->registry->expects(
            $this->at(0)
        )->method(
            'registry'
        )->with(
            'current_recurring_payment'
        )->will(
            $this->returnValue($payment)
        );
        $this->registry->expects(
            $this->at(1)
        )->method(
            'registry'
        )->with(
            'current_customer_id'
        )->will(
            $this->returnValue($customerId)
        );
        $payment->expects($this->once())->method('setStore')->with($store)->will($this->returnValue($payment));
        $this->storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->locale->expects($this->once())->method('formatDate')->will($this->returnValue('11-11-1999'));

        $this->recurringCollectionFilter->expects($this->once())
            ->method('byIds')
            ->will($this->returnValue($this->collection));

        $this->helper->expects($this->once())->method('formatCurrency')->will($this->returnValue('10 USD'));

        $pagerBlock = $this->getMockBuilder(
            'Magento\Theme\Block\Html\Pager'
        )->disableOriginalConstructor()->setMethods(
            array('setCollection')
        )->getMock();
        $pagerBlock->expects(
            $this->once()
        )->method(
            'setCollection'
        )->with(
            $this->collection
        )->will(
            $this->returnValue($pagerBlock)
        );
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface');
        $layout->expects($this->once())->method('createBlock')->will($this->returnValue($pagerBlock));
        $this->block->setLayout($layout);

        /**
         * @var \Magento\RecurringPayment\Block\Payment\Related\Orders\\Grid
         */
        $this->assertNotEmpty($this->block->getGridColumns());
        $expectedResult = array(
            new \Magento\Framework\Object(
                array(
                    'increment_id' => 1,
                    'increment_id_link_url' => null,
                    'created_at' => '11-11-1999',
                    'customer_name' => null,
                    'status' => null,
                    'base_grand_total' => '10 USD'
                )
            )
        );
        $this->assertEquals($expectedResult, $this->block->getGridElements());
    }
}
