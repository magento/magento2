<?php

namespace Magento\InstantPurchase\Test\Unit\CustomerData;

use Magento\InstantPurchase\CustomerData\InstantPurchase;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\InstantPurchase\Model\InstantPurchaseInterface;
use Magento\InstantPurchase\Model\InstantPurchaseOption;

class InstantPurchaseTest extends TestCase
{
    /**
     * @var InstantPurchase
     */
    private $instantPurchase;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var InstantPurchaseInterface|MockObject
     */
    private $instantPurchaseMock;
    
    /**
     * @var InstantPurchaseOption|MockObject
     */
    private $instantPurchaseOptionMock;
    
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instantPurchaseMock = $this->getMockBuilder(InstantPurchaseInterface::class)
            ->getMock();
        $this->instantPurchaseOptionMock = $this->getMockBuilder(InstantPurchaseOption::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->instantPurchase = $objectManager->getObject(
            InstantPurchase::class,
            [
                'customerSession' => $this->sessionMock,
                'storeManager' => $this->storeManagerMock,
                'instantPurchase' => $this->instantPurchaseMock,
            ]
        );
    }

    public function testGetSectionDataCustomerLoggedOut()
    {
        $this->sessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEquals(['available' => false], $this->instantPurchase->getSectionData());
    }

    public function testGetSectionDataLoggedInAndUnavailable()
    {
        $this->sessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        
        $this->sessionMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customerMock);
        
        $this->instantPurchaseMock->expects($this->once())
            ->method('getOption')
            ->with($this->storeMock, $this->customerMock)
            ->willReturn($this->instantPurchaseOptionMock);

        $this->instantPurchaseOptionMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $this->assertEquals(['available' => false], $this->instantPurchase->getSectionData());
    }

    public function testGetSectionData()
    {
        $this->sessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->sessionMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->instantPurchaseMock->expects($this->once())
            ->method('getOption')
            ->with($this->storeMock, $this->customerMock)
            ->willReturn($this->instantPurchaseOptionMock);

        $this->instantPurchaseOptionMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->assertTrue($this->instantPurchase->getSectionData()['available']);
    }
}
