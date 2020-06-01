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
     * @dataProvider dataProviderChildren
     */
    public function testApplyRulesWhenRuleWithStopRulesProcessingIsUsed($isChildren, $isContinue)
    {
        $positivePrice = 1;
        $skipValidation = false;
        $item = $this->getPreparedItem();
        $couponCode = 111;

        $ruleId = 1;
        $appliedRuleIds = [$ruleId => $ruleId];
        $discountData = $this->getMockBuilder(Data::class)
            ->setConstructorArgs(
                [
                    'amount' => 0,
                    'baseAmount' => 0,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0
                ]
            )
            ->getMock();
        $this->discountFactory->expects($this->any())
            ->method('create')
            ->with($this->anything())
            ->willReturn($discountData);
        /**
         * @var Rule|MockObject $ruleWithStopFurtherProcessing
         */
        $ruleWithStopFurtherProcessing = $this->getMockBuilder(Rule::class)
            ->addMethods(['getCouponType', 'getRuleId'])
            ->onlyMethods(['getStoreLabel', 'getActions'])
            ->disableOriginalConstructor()
            ->getMock();
        /**
         * @var Rule|MockObject $ruleThatShouldNotBeRun
         */
        $ruleThatShouldNotBeRun = $this->getMockBuilder(Rule::class)
            ->addMethods(['getStopRulesProcessing'])
            ->disableOriginalConstructor()
            ->getMock();

        $actionMock = $this->getMockBuilder(Collection::class)
            ->addMethods(['validate'])
            ->disableOriginalConstructor()
            ->getMock();

        $ruleWithStopFurtherProcessing->setName('ruleWithStopFurtherProcessing');
        $ruleThatShouldNotBeRun->setName('ruleThatShouldNotBeRun');
        $rules = [$ruleWithStopFurtherProcessing, $ruleThatShouldNotBeRun];

        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);

        $this->childrenValidationLocator->expects($this->any())
            ->method('isChildrenValidationRequired')
            ->willReturn(true);

        $this->validatorUtility->expects($this->atLeastOnce())
            ->method('canProcessRule')
            ->willReturn(true);

        $ruleWithStopFurtherProcessing->expects($this->atLeastOnce())
            ->method('getActions')
            ->willReturn($actionMock);
        $actionMock->expects($this->at(0))
            ->method('validate')
            ->with($item)
            ->willReturn(!$isChildren);

        // if there are child elements, check them
        if ($isChildren) {
            $item->expects($this->atLeastOnce())
                ->method('getChildren')
                ->willReturn([$item]);
            $actionMock->expects($this->at(1))
                ->method('validate')
                ->with($item)
                ->willReturn(!$isContinue);
            $product = $this->createPartialMock(Product::class, []);
            $item->expects($this->atLeastOnce())
                ->method('getProduct')
                ->willReturn($product);
        }

        if (!$isContinue || !$isChildren) {
            $ruleWithStopFurtherProcessing->expects($this->any())
                ->method('getRuleId')
                ->willReturn($ruleId);

            $this->applyRule($item, $ruleWithStopFurtherProcessing);

            $ruleWithStopFurtherProcessing->setStopRulesProcessing(true);
            $ruleThatShouldNotBeRun->expects($this->never())
                ->method('getStopRulesProcessing');
        }

        $result = $this->rulesApplier->applyRules($item, $rules, $skipValidation, $couponCode);
        $this->assertEquals($appliedRuleIds, $result);
    }

    public function testAddCouponDescriptionWithRuleDescriptionIsUsed()
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
    public function dataProviderChildren()
    {
        return [
            ['isChildren' => true, 'isContinue' => false],
            ['isChildren' => false, 'isContinue' => true],
        ];
    }

    /**
     * @return AbstractItem|MockObject
     */
    protected function getPreparedItem()
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
        $itemExtension = $this->getMockBuilder(
            ExtensionAttributesInterface::class
        )->setMethods(['setDiscounts', 'getDiscounts'])->getMock();
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
     * @param $item
     * @param $rule
     */
    protected function applyRule($item, $rule)
    {
        $qty = 2;
        $discountCalc = $this->createPartialMock(
            DiscountInterface::class,
            ['fixQuantity', 'calculate']
        );
        $discountData = $this->getMockBuilder(Data::class)
            ->setConstructorArgs(
                [
                    'amount' => 30,
                    'baseAmount' => 30,
                    'originalAmount' => 30,
                    'baseOriginalAmount' => 30
                ]
            )
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
