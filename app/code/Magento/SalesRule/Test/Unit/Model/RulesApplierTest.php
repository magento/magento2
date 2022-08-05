<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Event\Manager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Rule\Model\Action\Collection;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\DiscountInterface;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RulesApplierTest extends TestCase
{
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
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
        $this->rulesApplier = new RulesApplier(
            $this->calculatorFactory,
            $this->eventManager,
            $this->validatorUtility,
            $this->childrenValidationLocator,
            $this->discountFactory
        );
    }

    /**
     * @param bool $isChildren
     * @param bool $isContinue
     *
     * @return void
     * @dataProvider dataProviderChildren
     */
    public function testApplyRules(
        bool $isChildren,
        bool $isContinue
    ): void {
        $positivePrice = 1;
        $skipValidation = false;
        $item = $this->getPreparedItem();
        $couponCode = 111;

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
        $rule = $this->getMockBuilder(Rule::class)
            ->addMethods(['getCouponType', 'getRuleId'])
            ->onlyMethods(['getStoreLabel', 'getActions'])
            ->disableOriginalConstructor()
            ->getMock();

        $actionMock = $this->getMockBuilder(Collection::class)
            ->addMethods(['validate'])
            ->disableOriginalConstructor()
            ->getMock();

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
     * @return void
     */
    public function testAddCouponDescriptionWithRuleDescriptionIsUsed(): void
    {
        $ruleId = 1;
        $ruleDescription = 'Rule description';

        /**
         * @var Rule|MockObject $rule
         */
        $rule = $this->getMockBuilder(Rule::class)
            ->addMethods(['getCouponType', 'getRuleId'])
            ->onlyMethods(['getStoreLabel', 'getActions'])
            ->disableOriginalConstructor()
            ->getMock();

        $rule->setDescription($ruleDescription);

        /**
         * @var Address|MockObject $address
         */
        $address = $this->getMockBuilder(Address::class)
            ->addMethods(['setCouponCode', 'setAppliedRuleIds'])
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $description = $address->getDiscountDescriptionArray();
        $description[$ruleId] = $rule->getDescription();
        $address->setDiscountDescriptionArray($description[$ruleId]);

        $this->assertEquals($address->getDiscountDescriptionArray(), $description[$ruleId]);
    }

    /**
     * @return array
     */
    public function dataProviderChildren(): array
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
        $address = $this->getMockBuilder(Address::class)
            ->addMethods(['setCouponCode', 'setAppliedRuleIds'])
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        /**
         * @var AbstractItem|MockObject $item
         */
        $item = $this->getMockBuilder(Item::class)
            ->addMethods(['setDiscountAmount', 'setBaseDiscountAmount', 'setDiscountPercent', 'setAppliedRuleIds'])
            ->onlyMethods(['getAddress', 'getChildren', 'getExtensionAttributes', 'getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemExtension = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->addMethods(['setDiscounts', 'getDiscounts'])
            ->getMock();
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
}
