<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\SalesRule\Model\Coupon|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponMock;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    /**
     * @var
     */
    protected $ruleCustomerFactory;

    /**
     * @var \Magento\Framework\Locale\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolver;

    /**
     * @var \Magento\SalesRule\Model\Resource\Coupon\Usage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponUsage;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\SalesRule\Model\Resource\Report\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportRule;

    /**
     * @var \Magento\SalesRule\Model\Resource\Rule\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            'Magento\SalesRule\Model\Observer',
            [
                'coupon' => $this->couponMock,
                'ruleFactory' => $this->ruleFactory,
                'ruleCustomerFactory' => $this->ruleCustomerFactory,
                'localeResolver' => $this->localeResolver,
                'couponUsage' => $this->couponUsage,
                'localeDate' => $this->localeDate,
                'reportRule' => $this->reportRule,
                'collectionFactory' => $this->collectionFactory,
                'messageManager' => $this->messageManager
            ]
        );
    }

    protected function initMocks()
    {
        $this->couponMock = $this->getMock(
            '\Magento\SalesRule\Model\Coupon',
            [
                '__wakeup',
                'save',
                'load',
                'getId',
                'setTimesUsed',
                'getTimesUsed',
                'getRuleId',
                'loadByCode',
                'updateCustomerCouponTimesUsed'
            ],
            [],
            '',
            false
        );
        $this->ruleFactory = $this->getMock('Magento\SalesRule\Model\RuleFactory', ['create'], [], '', false);
        $this->ruleCustomerFactory = $this->getMock(
            'Magento\SalesRule\Model\Rule\CustomerFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->localeResolver = $this->getMock('Magento\Framework\Locale\Resolver', [], [], '', false);
        $this->couponUsage = $this->getMock('Magento\SalesRule\Model\Resource\Coupon\Usage', [], [], '', false);
        $this->localeDate = $this->getMock('Magento\Framework\Stdlib\DateTime\Timezone', ['date'], [], '', false);
        $this->reportRule = $this->getMock('Magento\SalesRule\Model\Resource\Report\Rule', [], [], '', false);
        $this->collectionFactory = $this->getMock(
            'Magento\SalesRule\Model\Resource\Rule\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->messageManager = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addWarning'],
            [],
            '',
            false
        );
    }

    /**
     * @param \\PHPUnit_Framework_MockObject_MockObject $observer
     * @return \PHPUnit_Framework_MockObject_MockObject $order
     */
    protected function initOrderFromEvent($observer)
    {
        $event = $this->getMock('Magento\Framework\Event', ['getOrder'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['getAppliedRuleIds', 'getCustomerId', 'getDiscountAmount', 'getCouponCode', '__wakeup'],
            [],
            '',
            false
        );

        $observer->expects($this->any())
            ->method('getEvent')
            ->will($this->returnValue($event));
        $event->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        return $order;
    }

    public function testSalesOrderAfterPlaceWithoutOrder()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->initOrderFromEvent($observer);

        $this->assertEquals($this->model, $this->model->salesOrderAfterPlace($observer));
    }

    public function testSalesOrderAfterPlaceWithoutRuleId()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $order = $this->initOrderFromEvent($observer);
        $discountAmount = 10;
        $order->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($discountAmount));

        $this->ruleFactory->expects($this->never())
            ->method('create');
        $this->assertEquals($this->model, $this->model->salesOrderAfterPlace($observer));
    }

    /**
     * @param int|bool $ruleCustomerId
     * @dataProvider salesOrderAfterPlaceDataProvider
     */
    public function testSalesOrderAfterPlace($ruleCustomerId)
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $rule = $this->getMock('Magento\SalesRule\Model\Rule', [], [], '', false);
        $ruleCustomer = $this->getMock(
            'Magento\SalesRule\Model\Rule\Customer',
            [
                'setCustomerId',
                'loadByCustomerRule',
                'getId',
                'setTimesUsed',
                'setRuleId',
                'save',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $order = $this->initOrderFromEvent($observer);
        $ruleId = 1;
        $couponId = 1;
        $customerId = 1;
        $discountAmount = 10;

        $order->expects($this->once())
            ->method('getAppliedRuleIds')
            ->will($this->returnValue($ruleId));
        $order->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($discountAmount));
        $order->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($rule));
        $rule->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($ruleId));
        $this->ruleCustomerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($ruleCustomer));
        $ruleCustomer->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($ruleCustomerId));
        $ruleCustomer->expects($this->any())
            ->method('setCustomerId')
            ->will($this->returnSelf());
        $ruleCustomer->expects($this->any())
            ->method('setRuleId')
            ->will($this->returnSelf());
        $this->couponMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($couponId));

        $this->couponUsage->expects($this->once())
            ->method('updateCustomerCouponTimesUsed')
            ->with($customerId, $couponId);

        $this->assertEquals($this->model, $this->model->salesOrderAfterPlace($observer));
    }

    public function salesOrderAfterPlaceDataProvider()
    {
        return [
            'With customer rule id' => [1],
            'Without customer rule id' => [null]
        ];
    }

    public function testAggregateSalesReportCouponsData()
    {
        $dateMock = $this->getMock('Magento\Framework\Stdlib\DateTime\DateInterface', [], [], '', false);
        $this->localeResolver->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeDate->expects($this->once())
            ->method('date')
            ->will($this->returnValue($dateMock));
        $dateMock->expects($this->once())
            ->method('subHour')
            ->with(25)
            ->will($this->returnSelf());
        $this->reportRule->expects($this->once())
            ->method('aggregate')
            ->with($dateMock);
        $this->localeResolver->expects($this->once())
            ->method('revert');

        $this->assertEquals($this->model, $this->model->aggregateSalesReportCouponsData());
    }

    protected function checkSalesRuleAvailability($attributeCode)
    {
        $collection = $this->getMock(
            'Magento\SalesRule\Model\Resource\Rule\Collection',
            ['addAttributeInConditionFilter', '__wakeup'],
            [],
            '',
            false
        );
        $rule = $this->getMock(
            'Magento\SalesRule\Model\Rule',
            ['setIsActive', 'getConditions', 'getActions', 'save', '__wakeup'],
            [],
            '',
            false
        );
        $combine = $this->getMock(
            'Magento\Rule\Model\Condition\Combine',
            ['getConditions', 'setConditions', '__wakeup'],
            [],
            '',
            false
        );
        $combineProduct = $this->getMock(
            'Magento\SalesRule\Model\Rule\Condition\Product',
            ['getAttribute', 'setConditions', '__wakeup'],
            [],
            '',
            false
        );

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));
        $collection->expects($this->once())
            ->method('addAttributeInConditionFilter')
            ->with($attributeCode)
            ->will($this->returnValue([$rule]));
        $rule->expects($this->once())
            ->method('setIsActive')
            ->with(0);
        $rule->expects($this->once())
            ->method('getConditions')
            ->will($this->returnValue($combine));
        $rule->expects($this->once())
            ->method('getActions')
            ->will($this->returnValue($combine));
        $combine->expects($this->at(0))
            ->method('getConditions')
            ->will($this->returnValue([$combine]));
        $combine->expects($this->at(1))
            ->method('getConditions')
            ->will($this->returnValue([$combineProduct]));
        $combine->expects($this->at(4))
            ->method('getConditions')
            ->will($this->returnValue([]));

        $combineProduct->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attributeCode));
        $combine->expects($this->any())
            ->method('setConditions')
            ->will(
                $this->returnValueMap(
                    [
                        [[], null],
                        [[$combine], null],
                        [[], null],
                    ]
                )
            );

        $this->messageManager->expects($this->once())
            ->method('addWarning')
            ->with(sprintf('1 Shopping Cart Price Rules based on "%s" attribute have been disabled.', $attributeCode));
    }

    public function testCatalogAttributeSaveAfter()
    {
        $attributeCode = 'attributeCode';
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $event = $this->getMock('Magento\Framework\Event', ['getAttribute', '__wakeup'], [], '', false);
        $attribute = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['dataHasChangedFor', 'getIsUsedForPromoRules', 'getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );

        $observer->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($event));
        $event->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));
        $attribute->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('is_used_for_promo_rules')
            ->will($this->returnValue(true));
        $attribute->expects($this->any())
            ->method('getIsUsedForPromoRules')
            ->will($this->returnValue(false));
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        $this->checkSalesRuleAvailability($attributeCode);

        $this->assertEquals($this->model, $this->model->catalogAttributeSaveAfter($observer));
    }

    public function testCatalogAttributeDeleteAfter()
    {
        $attributeCode = 'attributeCode';
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $event = $this->getMock('Magento\Framework\Event', ['getAttribute', '__wakeup'], [], '', false);
        $attribute = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['dataHasChangedFor', 'getIsUsedForPromoRules', 'getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );

        $observer->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($event));
        $event->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));
        $attribute->expects($this->any())
            ->method('getIsUsedForPromoRules')
            ->will($this->returnValue(true));
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        $this->checkSalesRuleAvailability($attributeCode);

        $this->assertEquals($this->model, $this->model->catalogAttributeDeleteAfter($observer));
    }

    public function testAddSalesRuleNameToOrderWithoutCouponCode()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getOrder'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['setCouponRuleName', 'getCouponCode', '__wakeup'],
            [],
            '',
            false
        );

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $this->couponMock->expects($this->never())
            ->method('loadByCode');

        $this->assertEquals($this->model, $this->model->addSalesRuleNameToOrder($observer));
    }

    public function testAddSalesRuleNameToOrderWithoutRule()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getOrder'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['setCouponRuleName', 'getCouponCode', '__wakeup'],
            [],
            '',
            false
        );
        $couponCode = 'coupon code';

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $order->expects($this->once())
            ->method('getCouponCode')
            ->will($this->returnValue($couponCode));
        $this->ruleFactory->expects($this->never())
            ->method('create');

        $this->assertEquals($this->model, $this->model->addSalesRuleNameToOrder($observer));
    }

    public function testAddSalesRuleNameToOrder()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getOrder'], [], '', false);
        $rule = $this->getMock('Magento\SalesRule\Model\Rule', ['load', 'getName', '__wakeup'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['setCouponRuleName', 'getCouponCode', '__wakeup'],
            [],
            '',
            false
        );
        $couponCode = 'coupon code';
        $ruleId = 1;

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $order->expects($this->once())
            ->method('getCouponCode')
            ->will($this->returnValue($couponCode));
        $this->couponMock->expects($this->once())
            ->method('getRuleId')
            ->will($this->returnValue($ruleId));
        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($rule));
        $rule->expects($this->once())
            ->method('load')
            ->with($ruleId)
            ->will($this->returnSelf());
        $order->expects($this->once())
            ->method('setCouponRuleName');

        $this->assertEquals($this->model, $this->model->addSalesRuleNameToOrder($observer));
    }
}
