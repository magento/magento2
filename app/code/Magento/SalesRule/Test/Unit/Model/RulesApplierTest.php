<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model;

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
use Magento\SalesRule\Model\Rule\Action\Discount\DiscountInterface;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @var CalculatorFactory|PHPUnit\Framework\MockObject\MockObject
     */
    protected $calculatorFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $discountFactory;

    /**
     * @var Manager|PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var Utility|PHPUnit\Framework\MockObject\MockObject
     */
    protected $validatorUtility;

    /**
     * @var ChildrenValidationLocator|PHPUnit\Framework\MockObject\MockObject
     */
    protected $childrenValidationLocator;

    protected function setUp(): void
    {
        $this->calculatorFactory = $this->createMock(
            CalculatorFactory::class
        );
        $this->discountFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory::class,
            ['create']
        );
        $this->eventManager = $this->createPartialMock(\Magento\Framework\Event\Manager::class, ['dispatch']);
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
        $discountData = $this->getMockBuilder(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class)
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
         * @var Rule|PHPUnit\Framework\MockObject\MockObject $ruleWithStopFurtherProcessing
         */
        $ruleWithStopFurtherProcessing = $this->createPartialMock(
            Rule::class,
            ['getStoreLabel', 'getCouponType', 'getRuleId', '__wakeup', 'getActions']
        );
        /**
         * @var Rule|PHPUnit\Framework\MockObject\MockObject $ruleThatShouldNotBeRun
        */
        $ruleThatShouldNotBeRun = $this->createPartialMock(
            Rule::class,
            ['getStopRulesProcessing', '__wakeup']
        );

        $actionMock = $this->createPartialMock(Collection::class, ['validate']);

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
        }

        //
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
         * @var Rule|PHPUnit\Framework\MockObject\MockObject $rule
         */
        $rule = $this->createPartialMock(
            Rule::class,
            ['getStoreLabel', 'getCouponType', 'getRuleId', '__wakeup', 'getActions']
        );

        $rule->setDescription($ruleDescription);

        /**
         * @var Address|PHPUnit\Framework\MockObject\MockObject $address
        */
        $address = $this->createPartialMock(
            Address::class,
            [
            'getQuote',
            'setCouponCode',
            'setAppliedRuleIds',
            '__wakeup'
            ]
        );
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
     * @return AbstractItem|PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPreparedItem()
    {
        /**
         * @var Address|PHPUnit\Framework\MockObject\MockObject $address
        */
        $address = $this->createPartialMock(
            Address::class,
            [
                'getQuote',
                'setCouponCode',
                'setAppliedRuleIds',
                '__wakeup'
            ]
        );
        /**
         * @var AbstractItem|PHPUnit\Framework\MockObject\MockObject $item
        */
        $item = $this->createPartialMock(
            Item::class,
            [
                'setDiscountAmount',
                'setBaseDiscountAmount',
                'setDiscountPercent',
                'getAddress',
                'setAppliedRuleIds',
                '__wakeup',
                'getChildren',
                'getExtensionAttributes'
            ]
        );
        $itemExtension = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensionAttributesInterface::class
        )->setMethods(['setDiscounts', 'getDiscounts'])->getMock();
        $itemExtension->method('getDiscounts')->willReturn([]);
        $itemExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getStore', '__wakeUp']);
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
            ->with($this->equalTo($qty), $this->equalTo($rule))
            ->willReturn($qty);

        $discountCalc->expects($this->any())
            ->method('calculate')
            ->with($this->equalTo($rule), $this->equalTo($item), $this->equalTo($qty))
            ->willReturn($discountData);
        $this->calculatorFactory->expects($this->any())
            ->method('create')
            ->with($this->anything())
            ->willReturn($discountCalc);
    }
}
