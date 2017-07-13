<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

/**
 * Class ValidatorTest
 * @@SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helper;

    /**
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $model;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\SalesRule\Model\RulesApplier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rulesApplier;

    /**
     * @var \Magento\SalesRule\Model\Validator\Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validators;

    /**
     * @var \Magento\SalesRule\Model\Utility|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $utility;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleCollection;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogData;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    protected function setUp()
    {
        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->rulesApplier = $this->createPartialMock(
            \Magento\SalesRule\Model\RulesApplier::class,
            ['setAppliedRuleIds', 'applyRules', 'addDiscountDescription', '__wakeup']
        );

        $this->addressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getShippingAmountForDiscount',
                    'getQuote',
                    'getCustomAttributesCodes',
                    'setCartFixedRules'
                ]
            )
            ->getMock();

        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $this->item = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['__wakeup', 'getAddress', 'getParentItemId']
        );
        $this->item->expects($this->any())
            ->method('getAddress')
            ->willReturn($this->addressMock);

        $context = $this->createMock(\Magento\Framework\Model\Context::class);
        $registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->catalogData = $this->createMock(\Magento\Catalog\Helper\Data::class);
        $this->utility = $this->createMock(\Magento\SalesRule\Model\Utility::class);
        $this->validators = $this->createPartialMock(\Magento\SalesRule\Model\Validator\Pool::class, ['getValidators']);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->ruleCollection = $this->getMockBuilder(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ruleCollectionFactoryMock = $this->prepareRuleCollectionMock($this->ruleCollection);

        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $this->model = $this->helper->getObject(
            \Magento\SalesRule\Model\Validator::class,
            [
                'context' => $context,
                'registry' => $registry,
                'collectionFactory' => $ruleCollectionFactoryMock,
                'catalogData' => $this->catalogData,
                'utility' => $this->utility,
                'rulesApplier' => $this->rulesApplier,
                'validators' => $this->validators,
                'messageManager' => $this->messageManager
            ]
        );
        $this->model->setWebsiteId(1);
        $this->model->setCustomerGroupId(2);
        $this->model->setCouponCode('code');
        $this->ruleCollection->expects($this->any())
            ->method('setValidationFilter')
            ->with(
                $this->model->getWebsiteId(),
                $this->model->getCustomerGroupId(),
                $this->model->getCouponCode(),
                null,
                $this->addressMock
            )
            ->willReturnSelf();
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteItemMock()
    {
        $fixturePath = __DIR__ . '/_files/';
        $itemDownloadable = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getAddress', '__wakeup']
        );
        $itemDownloadable->expects($this->any())->method('getAddress')->will($this->returnValue($this->addressMock));

        $itemSimple = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getAddress', '__wakeup']);
        $itemSimple->expects($this->any())->method('getAddress')->will($this->returnValue($this->addressMock));

        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getStoreId', '__wakeup']);
        $quote->expects($this->any())->method('getStoreId')->will($this->returnValue(1));

        $itemData = include $fixturePath . 'quote_item_downloadable.php';
        $itemDownloadable->addData($itemData);
        $quote->addItem($itemDownloadable);

        $itemData = include $fixturePath . 'quote_item_simple.php';
        $itemSimple->addData($itemData);
        $quote->addItem($itemSimple);

        return $itemDownloadable;
    }

    public function testCanApplyRules()
    {
        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $item = $this->getQuoteItemMock();
        $rule = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $actionsCollection = $this->createPartialMock(\Magento\Rule\Model\Action\Collection::class, ['validate']);
        $actionsCollection->expects($this->any())
            ->method('validate')
            ->with($item)
            ->willReturn(true);
        $rule->expects($this->any())
            ->method('getActions')
            ->willReturn($actionsCollection);
        $iterator = new \ArrayIterator([$rule]);
        $this->ruleCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->utility->expects($this->any())
            ->method('canProcessRule')
            ->with($rule, $this->anything())
            ->willReturn(true);

        $quote = $item->getQuote();
        $quote->setItemsQty(2);
        $quote->setVirtualItemsQty(1);

        $this->assertTrue($this->model->canApplyRules($item));

        $quote->setItemsQty(2);
        $quote->setVirtualItemsQty(2);

        $this->assertTrue($this->model->canApplyRules($item));
    }

    public function testProcess()
    {
        $negativePrice = -1;

        $this->item->setDiscountCalculationPrice($negativePrice);
        $this->item->setData('calculation_price', $negativePrice);

        $this->rulesApplier->expects($this->never())->method('applyRules');

        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $this->model->process($this->item);
    }

    public function testProcessWhenItemPriceIsNegativeDiscountsAreZeroed()
    {
        $negativePrice = -1;
        $nonZeroDiscount = 123;
        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );

        $this->item->setDiscountCalculationPrice($negativePrice);
        $this->item->setData('calculation_price', $negativePrice);

        $this->item->setDiscountAmount($nonZeroDiscount);
        $this->item->setBaseDiscountAmount($nonZeroDiscount);
        $this->item->setDiscountPercent($nonZeroDiscount);

        $this->model->process($this->item);

        $this->assertEquals(0, $this->item->getDiscountAmount());
        $this->assertEquals(0, $this->item->getBaseDiscountAmount());
        $this->assertEquals(0, $this->item->getDiscountPercent());
    }

    public function testApplyRulesThatAppliedRuleIdsAreCollected()
    {
        $positivePrice = 1;
        $ruleId1 = 123;
        $ruleId2 = 234;
        $expectedRuleIds = [$ruleId1 => $ruleId1, $ruleId2 => $ruleId2];
        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );

        $this->item->setDiscountCalculationPrice($positivePrice);
        $this->item->setData('calculation_price', $positivePrice);
        $this->model->setSkipActionsValidation(true);

        $this->rulesApplier->expects($this->once())
            ->method('applyRules')
            ->with(
                $this->equalTo($this->item),
                $this->equalTo($this->ruleCollection),
                $this->anything(),
                $this->anything()
            )
            ->will($this->returnValue($expectedRuleIds));
        $this->rulesApplier->expects($this->once())
            ->method('setAppliedRuleIds')
            ->with(
                $this->anything(),
                $expectedRuleIds
            );

        $this->model->process($this->item);
    }

    public function testInit()
    {
        $this->assertInstanceOf(
            \Magento\SalesRule\Model\Validator::class,
            $this->model->init(
                $this->model->getWebsiteId(),
                $this->model->getCustomerGroupId(),
                $this->model->getCouponCode()
            )
        );
    }

    public function testCanApplyDiscount()
    {
        $validator = $this->getMockBuilder(\Magento\Framework\Validator\AbstractValidator::class)
            ->setMethods(['isValid'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validators->expects($this->any())
            ->method('getValidators')
            ->with('discount')
            ->willReturn([$validator]);
        $validator->expects($this->any())
            ->method('isValid')
            ->with($this->item)
            ->willReturn(false);

        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $this->assertFalse($this->model->canApplyDiscount($this->item));
    }

    public function testInitTotalsCanApplyDiscount()
    {
        $rule = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule::class,
            ['getSimpleAction', 'getActions', 'getId']
        );
        $item1 = $this->getMockForAbstractClass(
            \Magento\Quote\Model\Quote\Item\AbstractItem::class,
            [],
            '',
            false,
            true,
            true,
            [
                '__clone',
                'getDiscountCalculationPrice',
                'getBaseDiscountCalculationPrice',
                'getCalculationPrice',
                'getParentItemId'
            ]
        );
        $item2 = clone $item1;
        $items = [$item1, $item2];

        $rule->expects($this->any())
            ->method('getSimpleAction')
            ->willReturn(\Magento\SalesRule\Model\Rule::CART_FIXED_ACTION);
        $iterator = new \ArrayIterator([$rule]);
        $this->ruleCollection->expects($this->once())->method('getIterator')->willReturn($iterator);
        $validator = $this->getMockBuilder(\Magento\Framework\Validator\AbstractValidator::class)
            ->setMethods(['isValid'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validators->expects($this->atLeastOnce())->method('getValidators')->with('discount')
            ->willReturn([$validator]);
        $validator->expects($this->at(0))->method('isValid')->with($item1)->willReturn(false);
        $validator->expects($this->at(1))->method('isValid')->with($item2)->willReturn(true);

        $item1->expects($this->any())->method('getParentItemId')->willReturn(false);
        $item1->expects($this->never())->method('getDiscountCalculationPrice');
        $item1->expects($this->never())->method('getBaseDiscountCalculationPrice');
        $item2->expects($this->any())->method('getParentItemId')->willReturn(false);
        $item2->expects($this->any())->method('getDiscountCalculationPrice')->willReturn(50);
        $item2->expects($this->once())->method('getBaseDiscountCalculationPrice')->willReturn(50);
        $this->utility->expects($this->once())->method('getItemQty')->willReturn(1);
        $this->utility->expects($this->any())->method('canProcessRule')->willReturn(true);

        $actionsCollection = $this->createPartialMock(\Magento\Rule\Model\Action\Collection::class, ['validate']);
        $actionsCollection->expects($this->at(0))->method('validate')->with($item1)->willReturn(true);
        $actionsCollection->expects($this->at(1))->method('validate')->with($item2)->willReturn(true);
        $rule->expects($this->any())->method('getActions')->willReturn($actionsCollection);
        $rule->expects($this->any())->method('getId')->willReturn(1);

        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $this->model->initTotals($items, $this->addressMock);
        $this->assertArrayHasKey('items_price', $this->model->getRuleItemTotalsInfo($rule->getId()));
        $this->assertArrayHasKey('base_items_price', $this->model->getRuleItemTotalsInfo($rule->getId()));
        $this->assertArrayHasKey('items_count', $this->model->getRuleItemTotalsInfo($rule->getId()));
        $this->assertEquals(1, $this->model->getRuleItemTotalsInfo($rule->getId())['items_count']);
    }

    public function testInitTotalsNoItems()
    {
        $address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $this->item->expects($this->never())
            ->method('getParentItemId');
        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $this->model->initTotals([], $address);
    }

    /**
     * @param $ruleCollection
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareRuleCollectionMock($ruleCollection)
    {
        $this->ruleCollection->expects($this->any())
            ->method('addFieldToFilter')
            ->with('is_active', 1)
            ->will($this->returnSelf());
        $this->ruleCollection->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $ruleCollectionFactoryMock =
            $this->getMockBuilder(\Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $ruleCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($ruleCollection));
        return $ruleCollectionFactoryMock;
    }

    public function testProcessShippingAmountNoRules()
    {
        $iterator = new \ArrayIterator([]);
        $this->ruleCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);
        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $this->assertInstanceOf(
            \Magento\SalesRule\Model\Validator::class,
            $this->model->processShippingAmount($this->setupAddressMock())
        );
    }

    public function testProcessShippingAmountProcessDisabled()
    {
        $ruleMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $iterator = new \ArrayIterator([$ruleMock]);
        $this->ruleCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);
        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $this->assertInstanceOf(
            \Magento\SalesRule\Model\Validator::class,
            $this->model->processShippingAmount($this->setupAddressMock())
        );
    }

    /**
     * @param string $action
     * @dataProvider dataProviderActions
     */
    public function testProcessShippingAmountActions($action)
    {
        $discountAmount = 50;

        $ruleMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApplyToShipping', 'getSimpleAction', 'getDiscountAmount'])
            ->getMock();
        $ruleMock->expects($this->any())
            ->method('getApplyToShipping')
            ->willReturn(true);
        $ruleMock->expects($this->any())
            ->method('getDiscountAmount')
            ->willReturn($discountAmount);
        $ruleMock->expects($this->any())
            ->method('getSimpleAction')
            ->willReturn($action);

        $iterator = new \ArrayIterator([$ruleMock]);
        $this->ruleCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->utility->expects($this->any())
            ->method('canProcessRule')
            ->willReturn(true);

        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $this->assertInstanceOf(
            \Magento\SalesRule\Model\Validator::class,
            $this->model->processShippingAmount($this->setupAddressMock(5))
        );
    }

    public static function dataProviderActions()
    {
        return [
            [\Magento\SalesRule\Model\Rule::TO_PERCENT_ACTION],
            [\Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION],
            [\Magento\SalesRule\Model\Rule::TO_FIXED_ACTION],
            [\Magento\SalesRule\Model\Rule::BY_FIXED_ACTION],
            [\Magento\SalesRule\Model\Rule::CART_FIXED_ACTION],
        ];
    }

    /**
     * @param null|int $shippingAmount
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function setupAddressMock($shippingAmount = null)
    {
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['setAppliedRuleIds', 'getStore'])
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $quoteMock->expects($this->any())
            ->method('setAppliedRuleIds')
            ->willReturnSelf();

        $this->addressMock->expects($this->any())
            ->method('getShippingAmountForDiscount')
            ->willReturn($shippingAmount);
        $this->addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->addressMock->expects($this->any())
            ->method('getCustomAttributesCodes')
            ->willReturn([]);
        return $this->addressMock;
    }

    public function testReset()
    {
        $this->utility->expects($this->once())
            ->method('resetRoundingDeltas');
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );
        $this->assertInstanceOf(\Magento\SalesRule\Model\Validator::class, $this->model->reset($addressMock));
    }
}
