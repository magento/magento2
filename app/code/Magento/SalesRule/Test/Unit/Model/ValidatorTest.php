<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @var Validator
     */
    protected $model;

    /**
     * @var \Magento\Quote\Model\Quote\Item|MockObject
     */
    protected $item;

    /**
     * @var \Magento\Quote\Model\Quote\Address|MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\SalesRule\Model\RulesApplier|MockObject
     */
    protected $rulesApplier;

    /**
     * @var \Magento\SalesRule\Model\Validator\Pool|MockObject
     */
    protected $validators;

    /**
     * @var \Magento\SalesRule\Model\Utility|MockObject
     */
    protected $utility;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection|MockObject
     */
    protected $ruleCollection;

    /**
     * @var \Magento\Catalog\Helper\Data|MockObject
     */
    protected $catalogData;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

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
                    'getBaseShippingAmountForDiscount',
                    'getQuote',
                    'getCustomAttributesCodes',
                    'setCartFixedRules'
                ]
            )
            ->getMock();

        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem|MockObject $item */
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
        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Validator|MockObject $validator */
        $this->model = $this->helper->getObject(
            Validator::class,
            [
                'context' => $context,
                'registry' => $registry,
                'collectionFactory' => $ruleCollectionFactoryMock,
                'catalogData' => $this->catalogData,
                'utility' => $this->utility,
                'rulesApplier' => $this->rulesApplier,
                'validators' => $this->validators,
                'messageManager' => $this->messageManager,
                'priceCurrency' => $this->priceCurrency
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
     * @return \Magento\Quote\Model\Quote\Item|MockObject
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

        /** @var $quote Quote */
        $quote = $this->createPartialMock(Quote::class, ['getStoreId', '__wakeup']);
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
        $rule = $this->createMock(Rule::class);
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
            Validator::class,
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
            Rule::class,
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
            ->willReturn(Rule::CART_FIXED_ACTION);
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
     * @return MockObject
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
            Validator::class,
            $this->model->processShippingAmount($this->setupAddressMock())
        );
    }

    public function testProcessShippingAmountProcessDisabled()
    {
        $ruleMock = $this->getMockBuilder(Rule::class)
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
            Validator::class,
            $this->model->processShippingAmount($this->setupAddressMock())
        );
    }

    /**
     * Tests shipping amounts according to rule simple action.
     *
     * @param string $action
     * @param int $ruleDiscount
     * @param int $shippingDiscount
     * @dataProvider dataProviderActions
     */
    public function testProcessShippingAmountActions($action, $ruleDiscount, $shippingDiscount): void
    {
        $shippingAmount = 5;

        $ruleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApplyToShipping', 'getSimpleAction', 'getDiscountAmount'])
            ->getMock();
        $ruleMock->method('getApplyToShipping')
            ->willReturn(true);
        $ruleMock->method('getDiscountAmount')
            ->willReturn($ruleDiscount);
        $ruleMock->method('getSimpleAction')
            ->willReturn($action);

        $iterator = new \ArrayIterator([$ruleMock]);
        $this->ruleCollection->method('getIterator')
            ->willReturn($iterator);

        $this->utility->method('canProcessRule')
            ->willReturn(true);

        $this->priceCurrency->method('convert')
            ->willReturn($ruleDiscount);

        $this->model->init(
            $this->model->getWebsiteId(),
            $this->model->getCustomerGroupId(),
            $this->model->getCouponCode()
        );

        $addressMock = $this->setupAddressMock($shippingAmount);

        self::assertInstanceOf(Validator::class, $this->model->processShippingAmount($addressMock));
        self::assertEquals($shippingDiscount, $addressMock->getShippingDiscountAmount());
    }

    /**
     * @return array
     */
    public static function dataProviderActions()
    {
        return [
            [Rule::TO_PERCENT_ACTION, 50, 2.5],
            [Rule::BY_PERCENT_ACTION, 50, 2.5],
            [Rule::TO_FIXED_ACTION, 5, 0],
            [Rule::BY_FIXED_ACTION, 5, 5],
            [Rule::CART_FIXED_ACTION, 5, 0],
        ];
    }

    /**
     * @param null|int $shippingAmount
     * @return MockObject
     */
    protected function setupAddressMock($shippingAmount = null)
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['setAppliedRuleIds', 'getStore'])
            ->getMock();

        $quoteMock->method('getStore')
            ->willReturn($storeMock);

        $quoteMock->method('setAppliedRuleIds')
            ->willReturnSelf();

        $this->addressMock->method('getShippingAmountForDiscount')
            ->willReturn($shippingAmount);

        $this->addressMock->method('getBaseShippingAmountForDiscount')
            ->willReturn($shippingAmount);

        $this->addressMock->method('getQuote')
            ->willReturn($quoteMock);

        $this->addressMock->method('getCustomAttributesCodes')
            ->willReturn([]);

        return $this->addressMock;
    }

    public function testReset()
    {
        $this->utility->expects($this->once())
            ->method('resetRoundingDeltas');
        $quoteMock = $this->getMockBuilder(Quote::class)
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
        $this->assertInstanceOf(Validator::class, $this->model->reset($addressMock));
    }
}
