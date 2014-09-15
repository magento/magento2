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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Observer\Backend;

class CustomerQuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\Backend\CustomerQuote
     */
    protected $customerQuote;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Config\Share
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\\Magento\Sales\Model\QuoteFactory
     */
    protected $quoteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\Observer
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event
     */
    protected $eventMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Customer\Model\Config\Share')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteFactoryMock = $this->getMockBuilder('\Magento\Sales\Model\QuoteFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getOrigCustomerDataObject', 'getCustomerDataObject'])
            ->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->eventMock));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->customerQuote = $objectManager->getObject(
            'Magento\Sales\Model\Observer\Backend\CustomerQuote',
            [
                'storeManager' => $this->storeManagerMock,
                'config' => $this->configMock,
                'quoteFactory' => $this->quoteFactoryMock,
            ]
        );
    }

    public function testDispatchNoCustomerGroupChange()
    {
        $customerDataObjectMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(1));
        $origCustomerDataObjectMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $origCustomerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(1));
        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObjectMock));
        $this->eventMock->expects($this->any())
            ->method('getOrigCustomerDataObject')
            ->will($this->returnValue($origCustomerDataObjectMock));
        $this->quoteFactoryMock->expects($this->never())
            ->method('create');

        $this->customerQuote->dispatch($this->observerMock);
    }

    /**
     * @param bool $isWebsiteScope
     * @param array $websites
     * @param int $quoteId
     * @dataProvider dispatchDataProvider
     */
    public function testDispatch($isWebsiteScope,$websites, $quoteId)
    {
        $this->configMock->expects($this->once())
            ->method('isWebsiteScope')
            ->will($this->returnValue($isWebsiteScope));
        $customerDataObjectMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(1));
        $customerDataObjectMock->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(2));
        if ($isWebsiteScope) {
            $websites = $websites[0];
            $this->storeManagerMock->expects($this->once())
                ->method('getWebsite')
                ->with(2)
                ->will($this->returnValue($websites));
        } else {
            $this->storeManagerMock->expects($this->once())
                ->method('getWebsites')
                ->will($this->returnValue($websites));
        }
        $origCustomerDataObjectMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $origCustomerDataObjectMock->expects($this->any())
            ->method('getGroupId')
            ->will($this->returnValue(2));
        $this->eventMock->expects($this->any())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObjectMock));
        $this->eventMock->expects($this->any())
            ->method('getOrigCustomerDataObject')
            ->will($this->returnValue($origCustomerDataObjectMock));
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote $quoteMock */
        $quoteMock = $this->getMockBuilder(
            'Magento\Sales\Model\Quote'
        )->setMethods(
                array(
                    'setWebsite',
                    'loadByCustomer',
                    'getId',
                    'setCustomerGroupId',
                    'collectTotals',
                    'save',
                    '__wakeup'
                )
            )->disableOriginalConstructor(
            )->getMock();
        $websiteCount = count($websites);
        $this->quoteFactoryMock->expects($this->exactly($websiteCount))
            ->method('create')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->exactly($websiteCount))
            ->method('setWebsite');
        $quoteMock->expects($this->exactly($websiteCount))
            ->method('loadByCustomer');
        $quoteMock->expects($this->exactly($websiteCount))
            ->method('getId')
            ->will($this->returnValue($quoteId));
        if ($quoteId) {
            $quoteMock->expects($this->exactly($websiteCount))
                ->method('setCustomerGroupId');
            $quoteMock->expects($this->exactly($websiteCount))
                ->method('collectTotals');
            $quoteMock->expects($this->exactly($websiteCount))
                ->method('save');
        } else {
            $quoteMock->expects($this->never())
                ->method('setCustomerGroupId');
            $quoteMock->expects($this->never())
                ->method('collectTotals');
            $quoteMock->expects($this->never())
                ->method('save');
        }
        $this->customerQuote->dispatch($this->observerMock);
    }

    public function dispatchDataProvider()
    {
        return [
            [true, ['website1'], 3],
            [true, ['website1', 'website2'], 3],
            [false, ['website1'], 3],
            [false, ['website1', 'website2'], 3],
            [true, ['website1'], null],
            [true, ['website1', 'website2'], null],
            [false, ['website1'], null],
            [false, ['website1', 'website2'], null],
        ];
    }
}
