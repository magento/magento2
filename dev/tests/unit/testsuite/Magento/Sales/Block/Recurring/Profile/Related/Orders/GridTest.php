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
 * @category    Magento
 * @package     Magento_Sales
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Recurring\Profile\Related\Orders;

/**
 * Test class for \Magento\Sales\Block\Recurring\Profile\Related\Orders\Grid
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testPrepareLayout()
    {
        $customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $customer->expects($this->once())->method('getId')->will($this->returnValue(1));

        $store = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionElement = $this->getMockBuilder('Magento\Sales\Model\Recurring\Profile')
            ->disableOriginalConstructor()
            ->setMethods(array('setLocale', 'getIncrementId'))
            ->getMock();
        $collectionElement->expects($this->once())->method('getIncrementId')
            ->will($this->returnValue(1));

        $collection = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('addFieldToFilter', 'addFieldToSelect', 'setOrder', 'addRecurringProfilesFilter',
                'getIterator'))
            ->getMock();
        $collection->expects($this->any())->method('addFieldToFilter')
            ->will($this->returnValue($collection));
        $collection->expects($this->once())->method('addFieldToSelect')
            ->will($this->returnValue($collection));
        $collection->expects($this->once())->method('addRecurringProfilesFilter')
            ->will($this->returnValue($collection));
        $collection->expects($this->once())->method('setOrder')
            ->will($this->returnValue($collection));
        $collection->expects($this->once())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator(array($collectionElement))));

        $profile = $this->getMockBuilder('Magento\Sales\Model\Recurring\Profile')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldLabel'))
            ->getMock();

        $registry = $this->getMockBuilder('Magento\Core\Model\Registry')
            ->disableOriginalConstructor()
            ->setMethods(array('registry'))
            ->getMock();
        $registry->expects($this->at(0))
            ->method('registry')
            ->with('current_recurring_profile')
            ->will($this->returnValue($profile));
        $registry->expects($this->at(1))
            ->method('registry')
            ->with('current_customer')
            ->will($this->returnValue($customer));

        $storeManager = $this->getMockBuilder('Magento\Core\Model\StoreManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getStore'))
            ->getMock();
        $storeManager->expects($this->once())->method('getStore')
            ->will($this->returnValue($store));

        $context = $this->_getContext();

        $block = $this->_objectManagerHelper->getObject(
            'Magento\Sales\Block\Recurring\Profile\Related\Orders\Grid',
            array(
                'profile' => $profile,
                'registry' => $registry,
                'storeManager' => $storeManager,
                'collection' => $collection,
                'context' => $context
            )
        );

        $pagerBlock = $this->getMockBuilder('Magento\Page\Block\Html\Pager')
            ->disableOriginalConstructor()
            ->setMethods(array('setCollection'))
            ->getMock();
        $pagerBlock->expects($this->once())->method('setCollection')
            ->with($collection)
            ->will($this->returnValue($pagerBlock));
        $layout = $this->_getMockLayout();
        $layout->expects($this->once())->method('createBlock')
            ->will($this->returnValue($pagerBlock));

        $block->setLayout($layout);

        $this->assertNotEmpty($block->getGridColumns());
        $expectedResult = array(new \Magento\Object(array(
            'increment_id' => 1,
            'increment_id_link_url' => null,
            'created_at'  => '11-11-1999',
            'customer_name' => null,
            'status' => null,
            'base_grand_total' => '10 USD'
        )));
        $this->assertEquals($expectedResult, $block->getGridElements());
    }

    /**
     * Get layout mock
     *
     * @return \Magento\View\LayoutInterface
     */
    protected function _getMockLayout()
    {
        $layout = $this->getMockBuilder('Magento\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('createBlock', 'getChildName', 'setChild'))
            ->getMockForAbstractClass();
        return $layout;
    }

    /**
     * Get context object
     *
     * @return \Magento\Core\Block\Template\Context
     */
    protected function _getContext()
    {
        $helper = $this->getMockBuilder('Magento\Customer\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('formatCurrency', 'formatDate'))
            ->getMock();
        $helper->expects($this->once())->method('formatCurrency')
            ->will($this->returnValue('10 USD'));
        $helper->expects($this->once())->method('formatDate')
            ->will($this->returnValue('11-11-1999'));

        $helperFactory = $this->getMockBuilder('Magento\Core\Model\Factory\Helper')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();
        $helperFactory->expects($this->any())->method('get')->will($this->returnValue($helper));

        /** @var  \Magento\Core\Block\Template\Context $context */
        $context = $this->_objectManagerHelper->getObject(
            'Magento\Core\Block\Template\Context',
            array('helperFactory' => $helperFactory)
        );
        return $context;
    }
}
