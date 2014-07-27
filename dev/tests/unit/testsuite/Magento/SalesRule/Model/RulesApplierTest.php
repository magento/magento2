<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\SalesRule\Model;

class RulesApplierTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->calculatorFactory = $this->getMock(
            'Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory',
            [],
            [],
            '',
            false
        );
        $this->eventManager = $this->getMock(
            'Magento\Framework\Event\Manager',
            ['dispatch'],
            [],
            '',
            false
        );
        $this->validatorUtility = $this->getMock(
            'Magento\SalesRule\Model\Utility',
            ['canProcessRule', 'minFix', 'deltaRoundingFix', 'getItemQty'],
            [],
            '',
            false
        );


        $this->rulesApplier = new \Magento\SalesRule\Model\RulesApplier(
            $this->calculatorFactory,
            $this->eventManager,
            $this->validatorUtility
        );
    }

    public function testApplyRulesWhenRuleWithStopRulesProcessingIsUsed()
    {
        $positivePrice = 1;
        $skipValidation = true;
        $item = $this->getPreparedItem();
        $couponCode = 111;

        $ruleId = 1;
        $appliedRuleIds = [$ruleId => $ruleId];

        /**
         * @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $ruleWithStopFurtherProcessing
         */
        $ruleWithStopFurtherProcessing = $this->getMock(
            'Magento\SalesRule\Model\Rule',
            ['getStoreLabel', 'getCouponType', 'getRuleId', '__wakeup'],
            [],
            '',
            false
        );
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $ruleThatShouldNotBeRun */
        $ruleThatShouldNotBeRun = $this->getMock(
            'Magento\SalesRule\Model\Rule',
            ['getStopRulesProcessing', '__wakeup'],
            [],
            '',
            false
        );

        $ruleWithStopFurtherProcessing->setName('ruleWithStopFurtherProcessing');
        $ruleThatShouldNotBeRun->setName('ruleThatShouldNotBeRun');
        $rules = [$ruleWithStopFurtherProcessing, $ruleThatShouldNotBeRun];

        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);

        $this->validatorUtility->expects($this->atLeastOnce())
            ->method('canProcessRule')
            ->will(
                $this->returnValue(true)
            );
        $ruleWithStopFurtherProcessing->expects($this->any())
            ->method('getRuleId')
            ->will($this->returnValue($ruleId));
        $this->applyRule($item, $ruleWithStopFurtherProcessing);
        $ruleWithStopFurtherProcessing->setStopRulesProcessing(true);
        $ruleThatShouldNotBeRun->expects($this->never())
            ->method('getStopRulesProcessing');
        $result = $this->rulesApplier->applyRules($item, $rules, $skipValidation, $couponCode);
        $this->assertEquals($appliedRuleIds, $result);
    }

    /**
     * @return \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPreparedItem()
    {
        /** @var \Magento\Sales\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            [
                'getQuote',
                'setCouponCode',
                'setAppliedRuleIds',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            [
                'setDiscountAmount',
                'setBaseDiscountAmount',
                'setDiscountPercent',
                'getAddress',
                'setAppliedRuleIds',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $quote = $this->getMock('Magento\Sales\Model\Quote', ['getStore', '__wakeUp'], [], '', false);
        $item->expects($this->any())->method('getAddress')->will($this->returnValue($address));
        $address->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quote));

        return $item;
    }

    protected function applyRule($item, $rule)
    {
        $qty = 2;
        $discountCalc = $this->getMock(
            'Magento\SalesRule\Model\Rule\Action\Discount',
            ['fixQuantity', 'calculate'],
            [],
            '',
            false
        );
        $discountData = $this->getMock(
            'Magento\SalesRule\Model\Rule\Action\Discount\Data',
            [],
            [
                'amount' => 30,
                'baseAmount' => 30,
                'originalAmount' => 30,
                'baseOriginalAmount' => 30
            ],
            '',
            false
        );
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
