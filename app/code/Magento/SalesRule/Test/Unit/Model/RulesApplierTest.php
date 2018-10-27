<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RulesApplierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorFactory;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\SalesRule\Model\Utility|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorUtility;

    /**
     * @var \Magento\SalesRule\Model\Quote\ChildrenValidationLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $childrenValidationLocator;

    protected function setUp()
    {
        $this->calculatorFactory = $this->createMock(
            \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory::class
        );
        $this->eventManager = $this->createPartialMock(\Magento\Framework\Event\Manager::class, ['dispatch']);
        $this->validatorUtility = $this->createPartialMock(
            \Magento\SalesRule\Model\Utility::class,
            ['canProcessRule', 'minFix', 'deltaRoundingFix', 'getItemQty']
        );
        $this->childrenValidationLocator = $this->createPartialMock(
            \Magento\SalesRule\Model\Quote\ChildrenValidationLocator::class,
            ['isChildrenValidationRequired']
        );
        $this->rulesApplier = new \Magento\SalesRule\Model\RulesApplier(
            $this->calculatorFactory,
            $this->eventManager,
            $this->validatorUtility,
            $this->childrenValidationLocator
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

        /**
         * @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $ruleWithStopFurtherProcessing
         */
        $ruleWithStopFurtherProcessing = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule::class,
            ['getStoreLabel', 'getCouponType', 'getRuleId', '__wakeup', 'getActions']
        );
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $ruleThatShouldNotBeRun */
        $ruleThatShouldNotBeRun = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule::class,
            ['getStopRulesProcessing', '__wakeup']
        );

        $actionMock = $this->createPartialMock(\Magento\Rule\Model\Action\Collection::class, ['validate']);

        $ruleWithStopFurtherProcessing->setName('ruleWithStopFurtherProcessing');
        $ruleThatShouldNotBeRun->setName('ruleThatShouldNotBeRun');
        $rules = [$ruleWithStopFurtherProcessing, $ruleThatShouldNotBeRun];

        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);

        $this->childrenValidationLocator->expects($this->any())
            ->method('isChildrenValidationRequired')
            ->will($this->returnValue(true));

        $this->validatorUtility->expects($this->atLeastOnce())
            ->method('canProcessRule')
            ->will($this->returnValue(true));

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
                ->will($this->returnValue($ruleId));

            $this->applyRule($item, $ruleWithStopFurtherProcessing);

            $ruleWithStopFurtherProcessing->setStopRulesProcessing(true);
            $ruleThatShouldNotBeRun->expects($this->never())
                ->method('getStopRulesProcessing');
        }

        $result = $this->rulesApplier->applyRules($item, $rules, $skipValidation, $couponCode);
        $this->assertEquals($appliedRuleIds, $result);
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
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPreparedItem()
    {
        /** @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, [
                'getQuote',
                'setCouponCode',
                'setAppliedRuleIds',
                '__wakeup'
            ]);
        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, [
                'setDiscountAmount',
                'setBaseDiscountAmount',
                'setDiscountPercent',
                'getAddress',
                'setAppliedRuleIds',
                '__wakeup',
                'getChildren'
            ]);
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getStore', '__wakeUp']);
        $item->expects($this->any())->method('getAddress')->will($this->returnValue($address));
        $address->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quote));

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
            \Magento\SalesRule\Model\Rule\Action\Discount\DiscountInterface::class,
            ['fixQuantity', 'calculate']
        );
        $discountData = $this->getMockBuilder(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class)
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
            ->will($this->returnValue($qty));
        $discountCalc->expects($this->any())
            ->method('fixQuantity')
            ->with($this->equalTo($qty), $this->equalTo($rule))
            ->will($this->returnValue($qty));

        $discountCalc->expects($this->any())
            ->method('calculate')
            ->with($this->equalTo($rule), $this->equalTo($item), $this->equalTo($qty))
            ->will($this->returnValue($discountData));
        $this->calculatorFactory->expects($this->any())
            ->method('create')
            ->with($this->anything())
            ->will($this->returnValue($discountCalc));
    }
}
