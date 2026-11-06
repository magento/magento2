<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Rule\Model\Action\Collection;
use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\DiscountInterface;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\SelectRuleCoupon;
use Magento\SalesRule\Model\Utility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RulesApplierTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var RulesApplier
     */
    protected $rulesApplier;

    /**
     * @var CalculatorFactory|MockObject
     */
    protected $calculatorFactory;

    /**
     * @var DataFactory|MockObject
     */
    protected $discountFactory;

    /**
     * @var Manager|MockObject
     */
    protected $eventManager;

    /**
     * @var Utility|MockObject
     */
    protected $validatorUtility;

    /**
     * @var ChildrenValidationLocator|MockObject
     */
    protected $childrenValidationLocator;

    /**
     * @var RuleDiscountInterfaceFactory|MockObject
     */
    protected $ruleDiscountInterfaceFactoryMock;

    /**
     * @var DiscountDataInterfaceFactory|MockObject
     */
    protected $discountDataInterfaceFactoryMock;

    /**
     * @var SelectRuleCoupon|MockObject
     */
    protected $selectRuleCouponMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                RuleDiscountInterfaceFactory::class,
                $this->createMock(RuleDiscountInterfaceFactory::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->calculatorFactory = $this->createMock(
            CalculatorFactory::class
        );
        $this->discountFactory = $this->createPartialMock(
            DataFactory::class,
            ['create']
        );
        $this->eventManager = $this->createPartialMock(Manager::class, ['dispatch']);
        $this->validatorUtility = $this->createPartialMock(
            Utility::class,
            ['canProcessRule', 'minFix', 'deltaRoundingFix', 'getItemQty']
        );
        $this->childrenValidationLocator = $this->createPartialMock(
            ChildrenValidationLocator::class,
            ['isChildrenValidationRequired']
        );
        $this->ruleDiscountInterfaceFactoryMock = $this->getMockBuilder(RuleDiscountInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->discountDataInterfaceFactoryMock = $this->getMockBuilder(DiscountDataInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->selectRuleCouponMock = $this->getMockBuilder(SelectRuleCoupon::class)
                                        ->disableOriginalConstructor()
                                        ->onlyMethods(['execute'])
                                        ->getMock();

        $this->rulesApplier = new RulesApplier(
            $this->calculatorFactory,
            $this->eventManager,
            $this->validatorUtility,
            $this->childrenValidationLocator,
            $this->discountFactory,
            $this->ruleDiscountInterfaceFactoryMock,
            $this->discountDataInterfaceFactoryMock,
            $this->selectRuleCouponMock
        );
    }

    /**
     * @param bool $isChildren
     * @param bool $isContinue
     *
     * @return void
     */
    #[DataProvider('dataProviderChildren')]
    public function testApplyRules(
        bool $isChildren,
        bool $isContinue
    ): void {
        $positivePrice = 1;
        $skipValidation = false;
        $item = $this->getPreparedItem();
        $couponCode = [111];

        $ruleId = 1;
        $appliedRuleIds = [$ruleId => $ruleId];
        $discountData = $this->getMockBuilder(Data::class)
            ->getMock();
        $this->discountFactory->expects($this->any())
            ->method('create')
            ->with($this->anything())
            ->willReturn($discountData);
        /**
         * @var Rule|MockObject $rule
         */
        $rule = $this->createPartialMockWithReflection(
            Rule::class,
            ['getCouponType', 'getRuleId', 'getStoreLabel', 'getActions']
        );

        $actionMock = $this->createPartialMockWithReflection(
            Collection::class,
            ['validate']
        );

        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);

        $this->childrenValidationLocator->expects($this->any())
            ->method('isChildrenValidationRequired')
            ->willReturn(true);

        $this->validatorUtility->expects($this->atLeastOnce())
            ->method('canProcessRule')
            ->willReturn(true);

        $rule->expects($this->atLeastOnce())
            ->method('getActions')
            ->willReturn($actionMock);

        // if there are child elements, check them
        if ($isChildren) {
            $item->expects($this->atLeastOnce())
                ->method('getChildren')
                ->willReturn([$item]);
            $actionMock->method('validate')
                ->with($item)
                ->willReturn(!$isContinue);
            $product = $this->createPartialMock(Product::class, []);
            $item->expects($this->atLeastOnce())
                ->method('getProduct')
                ->willReturn($product);
        } else {
            $actionMock->method('validate')
                ->with($item)
                ->willReturn(!$isChildren);
        }

        if (!$isContinue || !$isChildren) {
            $rule->expects($this->any())
                ->method('getRuleId')
                ->willReturn($ruleId);

            $this->applyRule($item, $rule);
        }

        $result = $this->rulesApplier->applyRules($item, [$rule], $skipValidation, $couponCode);
        $this->assertEquals($appliedRuleIds, $result);
    }

    /**
     * @param string $storeLabel
     * @param string|null $selectedCoupon
     * @param string|null $ruleDescription
     * @param array $couponCodes
     * @param array $expected
     * @return void
     */
    #[DataProvider('discountDescriptionProvider')]
    public function testAddDiscountDescription(
        string $storeLabel,
        ?string $selectedCoupon,
        ?string $ruleDescription,
        array $couponCodes,
        array $expected
    ): void {
        $address = $this->getDescriptionAddress();
        $rule = $this->getDescriptionRule(1, $storeLabel, $ruleDescription);
        $this->selectRuleCouponMock->method('execute')->willReturnMap([
            [$rule, $couponCodes, $selectedCoupon],
        ]);

        self::assertSame(
            $this->rulesApplier,
            $this->rulesApplier->addDiscountDescription($address, $rule, $couponCodes)
        );
        self::assertSame($expected, (array)$address->getDiscountDescriptionArray());
    }

    /**
     * @return array
     */
    public static function discountDescriptionProvider(): array
    {
        return [
            'store label overrides coupon' => [
                'Merchant label', 'SAVE10', 'Internal description', ['SAVE10'], [1 => 'Merchant label'],
            ],
            'coupon overrides description' => [
                '', 'SAVE10', 'Internal description', ['SAVE10'], [1 => 'SAVE10'],
            ],
            'coupon without description' => [
                '', 'SAVE10', null, ['SAVE10'], [1 => 'SAVE10'],
            ],
            'automatic rule description is not displayed' => [
                '', null, 'Automatic promotion', [], [],
            ],
            'unrelated coupon does not produce a label' => [
                '', null, 'Automatic promotion', ['OTHER'], [],
            ],
            'no label coupon or description' => [
                '', null, null, [], [],
            ],
        ];
    }

    /**
     * @return void
     */
    public function testAddDiscountDescriptionPreservesMultipleRuleLabels(): void
    {
        $address = $this->getDescriptionAddress();
        $automaticRule = $this->getDescriptionRule(1, 'Merchant label', 'Automatic description');
        $couponRule = $this->getDescriptionRule(2, '', 'Internal description');
        $unlabeledRule = $this->getDescriptionRule(3, '', 'Internal automatic description');
        $couponCodes = ['SAVE10'];
        $this->selectRuleCouponMock->method('execute')->willReturnMap([
            [$couponRule, $couponCodes, 'SAVE10'],
            [$unlabeledRule, $couponCodes, null],
        ]);

        $this->rulesApplier->addDiscountDescription($address, $automaticRule, $couponCodes);
        $this->rulesApplier->addDiscountDescription($address, $couponRule, $couponCodes);
        $this->rulesApplier->addDiscountDescription($address, $couponRule, $couponCodes);
        $this->rulesApplier->addDiscountDescription($address, $unlabeledRule, $couponCodes);

        self::assertSame([1 => 'Merchant label', 2 => 'SAVE10'], $address->getDiscountDescriptionArray());
    }

    /**
     * @return Address
     */
    private function getDescriptionAddress(): Address
    {
        $data = [];
        $address = $this->createPartialMock(Address::class, ['getQuote', 'setData', 'getData']);
        $address->method('getQuote')->willReturn($this->createStub(Quote::class));
        $address->method('setData')->willReturnCallback(
            function ($key, $value = null) use (&$data, &$address) {
                $data[$key] = $value;
                return $address;
            }
        );
        $address->method('getData')->willReturnCallback(
            function ($key = '') use (&$data) {
                return $data[$key] ?? null;
            }
        );

        return $address;
    }

    /**
     * @param int $id
     * @param string $storeLabel
     * @param string|null $description
     * @return Rule
     */
    private function getDescriptionRule(int $id, string $storeLabel, ?string $description): Rule
    {
        $rule = $this->createPartialMock(Rule::class, ['getId', 'getStoreLabel']);
        $rule->method('getId')->willReturn($id);
        $rule->method('getStoreLabel')->willReturn($storeLabel);
        $rule->setDescription($description);

        return $rule;
    }

    /**
     * @return array
     */
    public static function dataProviderChildren(): array
    {
        return [
            ['isChildren' => true, 'isContinue' => false],
            ['isChildren' => false, 'isContinue' => true]
        ];
    }

    /**
     * @return AbstractItem|MockObject
     */
    protected function getPreparedItem(): AbstractItem
    {
        /**
         * @var Address|MockObject $address
         */
        $address = $this->createPartialMockWithReflection(
            Address::class,
            ['setCouponCode', 'setAppliedRuleIds', 'getQuote']
        );
        /**
         * @var AbstractItem|MockObject $item
         */
        $item = $this->createPartialMockWithReflection(
            Item::class,
            [
                'setDiscountAmount',
                'setBaseDiscountAmount',
                'setDiscountPercent',
                'setAppliedRuleIds',
                'getAppliedRuleIds',
                'getAddress',
                'getChildren',
                'getExtensionAttributes',
                'getProduct',
                'getQuote'
            ]
        );
        $itemExtension = $this->createPartialMockWithReflection(
            ExtensionAttributesInterface::class,
            ['setDiscounts', 'getDiscounts']
        );
        $itemExtension->method('getDiscounts')->willReturn([]);
        $itemExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $quote = $this->createPartialMock(Quote::class, ['getStore']);
        $item->expects($this->any())->method('getAddress')->willReturn($address);
        $item->expects($this->any())->method('getExtensionAttributes')->willReturn($itemExtension);
        $address->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);
        $item->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);

        return $item;
    }

    /**
     * @param MockObject $item
     * @param MockObject $rule
     *
     * @return void
     */
    protected function applyRule(MockObject $item, MockObject $rule): void
    {
        $qty = 2;
        $discountCalc = $this->createPartialMock(
            DiscountInterface::class,
            ['fixQuantity', 'calculate']
        );
        $discountData = $this->getMockBuilder(Data::class)
            ->getMock();
        $this->validatorUtility->expects($this->any())
            ->method('getItemQty')
            ->with($this->anything(), $this->anything())
            ->willReturn($qty);
        $discountCalc->expects($this->any())
            ->method('fixQuantity')
            ->with($qty, $rule)
            ->willReturn($qty);

        $discountCalc->expects($this->any())
            ->method('calculate')
            ->with($rule, $item, $qty)
            ->willReturn($discountData);
        $this->calculatorFactory->expects($this->any())
            ->method('create')
            ->with($this->anything())
            ->willReturn($discountCalc);
    }

    public function testSetAppliedRuleIds()
    {
        $item = $this->getPreparedItem();
        $ruleId = 1;
        $appliedRuleIds = [$ruleId => $ruleId];
        $previouslyAppliedRuleIds = '3';
        $expectedAppliedRuleIds = '3,1';

        $item->expects($this->once())
            ->method('setAppliedRuleIds')
            ->with($expectedAppliedRuleIds);
        $item->expects($this->once())
            ->method('getAppliedRuleIds')
            ->willReturn($previouslyAppliedRuleIds);

        $this->rulesApplier->setAppliedRuleIds($item, $appliedRuleIds);
    }
}
