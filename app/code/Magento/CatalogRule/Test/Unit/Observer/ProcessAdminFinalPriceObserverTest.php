<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\CatalogRule\Observer\ProcessAdminFinalPriceObserver;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\DataType\Date;
use PHPUnit\Framework\TestCase;

/**
 * Class ProcessAdminFinalPriceObserverTest
 *
 * Test class for Observer for applying catalog rules on product for admin area
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessAdminFinalPriceObserverTest extends TestCase
{
    /**
     * @var ProcessAdminFinalPriceObserver
     */
    private $observer;

    /**
     * Store Manager mock
     *
     * @var StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * Locale Date mock
     *
     * @var TimezoneInterface
     */
    private $localeDateMock;

    /**
     * Resource Rule Factory mock
     *
     * @var RuleFactory
     */
    private $resourceRuleFactoryMock;

    /**
     * Rule Prices Storage mock
     *
     * @var RulePricesStorage
     */
    private $rulePricesStorageMock;

    /**
     * @var Event|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventMock;

    /**
     * @var Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $observerMock;

    protected function setUp(): void
    {
        $this->observerMock = $this
            ->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this
            ->getMockBuilder(Event::class)
            ->setMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rulePricesStorageMock = $this->getMockBuilder(RulePricesStorage::class)
            ->setMethods(['getWebsiteId', 'getRulePrice', 'getCustomerGroupId', 'setRulePrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceRuleFactoryMock = $this->getMockBuilder(RuleFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->setMethods(['scopeDate'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerHelper = new ObjectManager($this);
        $this->observer = $objectManagerHelper->getObject(
            ProcessAdminFinalPriceObserver::class,
            [
                'rulePricesStorage' => $this->rulePricesStorageMock,
                'storeManager' => $this->storeManagerMock,
                'resourceRuleFactory' => $this->resourceRuleFactoryMock,
                'localeDate' => $this->localeDateMock
            ]
        );
    }

    public function testExecute()
    {
        $finalPrice = 20.00;
        $rulePrice = 10.00;
        $storeId = 2;
        $wId = 1;
        $gId = 4;
        $pId = 20;
        $localeDateFormat = 'Y-m-d H:i:s';
        $date = '2019-12-02 08:00:00';
        $storeMock = $this->createMock(Store::class);
        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $productMock = $this->getMockBuilder(Product::class)
            ->setMethods(
                [
                    'getStoreId',
                    'getWebsiteId',
                    'getId',
                    'getData',
                    'getCustomerGroupId',
                    'setFinalPrice'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $dateMock = $this->getMockBuilder(Date::class)
            ->setMethods(['format'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDateMock->expects($this->once())
            ->method('scopeDate')
            ->with($storeId)
            ->willReturn($dateMock);
        $dateMock->expects($this->once())
            ->method('format')
            ->with($localeDateFormat)
            ->willReturn($date);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($wId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $productMock->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn($gId);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($pId);
        $productMock->expects($this->once())
            ->method('getData')
            ->with('final_price')
            ->willReturn($finalPrice);
        $this->rulePricesStorageMock->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn($gId);
        $this->resourceRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->rulePricesStorageMock);
        $this->rulePricesStorageMock->expects($this->any())
            ->method('getRulePrice')
            ->willReturn($rulePrice);
        $this->rulePricesStorageMock->expects($this->once())
            ->method('setRulePrice')
            ->willReturnSelf();
        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->assertEquals($this->observer, $this->observer->execute($this->observerMock));
    }
}
