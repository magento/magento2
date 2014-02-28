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
 * @category    Magento
 * @package     Magento_SalesRule
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\SalesRule\Model;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    protected function setUp()
    {
        $this->model = $this->getMock(
            'Magento\SalesRule\Model\Validator',
            array('_getRules', '_getItemOriginalPrice', '_getItemBaseOriginalPrice', '__wakeup'),
            array(),
            '',
            false
        );
        $this->model->expects($this->any())
            ->method('_getRules')
            ->will($this->returnValue(array()));
        $this->model->expects($this->any())
            ->method('_getItemOriginalPrice')
            ->will($this->returnValue(1));
        $this->model->expects($this->any())
            ->method('_getItemBaseOriginalPrice')
            ->will($this->returnValue(1));
    }

    /**
     * @return \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteItemMock()
    {
        $fixturePath = __DIR__ . '/_files/';
        $itemDownloadable = $this->getMock('Magento\Sales\Model\Quote\Item', ['getAddress', '__wakeup'], [], '', false);
        $itemDownloadable->expects($this->any())
            ->method('getAddress')
            ->will($this->returnValue(new \stdClass()));

        $itemSimple = $this->getMock('Magento\Sales\Model\Quote\Item', ['getAddress', '__wakeup'], [], '', false);
        $itemSimple->expects($this->any())
            ->method('getAddress')
            ->will($this->returnValue(new \stdClass()));

        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $this->getMock('Magento\Sales\Model\Quote', ['hasNominalItems', '__wakeup'], [], '', false);
        $quote->expects($this->any())
            ->method('hasNominalItems')
            ->will($this->returnValue(false));

        $itemData = include($fixturePath . 'quote_item_downloadable.php');
        $itemDownloadable->addData($itemData);
        $quote->addItem($itemDownloadable);

        $itemData = include($fixturePath . 'quote_item_simple.php');
        $itemSimple->addData($itemData);
        $quote->addItem($itemSimple);

        return $itemDownloadable;
    }

    public function testCanApplyRules()
    {
        $item = $this->getQuoteItemMock();

        $quote = $item->getQuote();
        $quote->setItemsQty(2);
        $quote->setVirtualItemsQty(1);

        $this->assertTrue($this->model->canApplyRules($item));

        $quote->setItemsQty(2);
        $quote->setVirtualItemsQty(2);

        $this->assertTrue($this->model->canApplyRules($item));

        return true;
    }

    public function testProcessFreeShipping()
    {
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array('getAddress', '__wakeup'), array(), '', false);
        $item->expects($this->once())
            ->method('getAddress')
            ->will($this->returnValue(true));

        $this->assertInstanceOf('Magento\SalesRule\Model\Validator', $this->model->processFreeShipping($item));

        return true;
    }

    public function testProcessWhenItemPriceIsNegativeRulesAreNotApplied()
    {
        $negativePrice = -1;

        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMock('Magento\SalesRule\Model\Validator', ['applyRules', '__wakeup'], [], '', false);

        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array('__wakeup'), array(), '', false);

        // 2. Set fixtures
        $item->setDiscountCalculationPrice($negativePrice);
        $item->setData('calculation_price', $negativePrice);

        // 3. Set expectations
        $validator->expects($this->never())->method('applyRules');

        // 4. Run tested method
        $validator->process($item);
    }

    public function testProcessWhenItemPriceIsNegativeDiscountsAreZeroed()
    {
        $negativePrice = -1;
        $nonZeroDiscount = 123;

        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMock('Magento\SalesRule\Model\Validator', ['applyRules', '__wakeup'], [], '', false);

        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array('__wakeup'), array(), '', false);

        // 2. Set fixtures
        $item->setDiscountCalculationPrice($negativePrice);
        $item->setData('calculation_price', $negativePrice);

        // Discounts that could be set before running tested method
        $item->setDiscountAmount($nonZeroDiscount);
        $item->setBaseDiscountAmount($nonZeroDiscount);
        $item->setDiscountPercent($nonZeroDiscount);

        // 3. Run tested method
        $validator->process($item);

        // 4. Check expected result
        $this->assertEquals(0, $item->getDiscountAmount());
        $this->assertEquals(0, $item->getBaseDiscountAmount());
        $this->assertEquals(0, $item->getDiscountPercent());
    }

    public function testApplyRulesWhenRuleWithStopRulesProcessingIsUsed()
    {
        $positivePrice = 1;

        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMock(
            'Magento\SalesRule\Model\Validator',
            array('applyRule', 'setAppliedRuleIds', '_canProcessRule', '_getRules', '__wakeup'),
            array(),
            '',
            false
        );
        /** @var \Magento\Sales\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMock('Magento\Sales\Model\Quote\Address', ['__wakeup'], [], '', false);
        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', ['getAddress', '__wakeup'], [], '', false);
        /**
         * @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $ruleWithStopFurtherProcessing
         */
        $ruleWithStopFurtherProcessing = $this->getMock('Magento\SalesRule\Model\Rule', ['__wakeup'], [], '', false);
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $ruleThatShouldNotBeRun */
        $ruleThatShouldNotBeRun = $this->getMock('Magento\SalesRule\Model\Rule', array('__wakeup'), array(), '', false);

        $item->expects($this->any())->method('getAddress')->will($this->returnValue($address));
        $ruleWithStopFurtherProcessing->setName('ruleWithStopFurtherProcessing');
        $ruleThatShouldNotBeRun->setName('ruleThatShouldNotBeRun');
        $rules = array(
            $ruleWithStopFurtherProcessing,
            $ruleThatShouldNotBeRun,
        );
        $validator->expects($this->any())->method('_getRules')->will($this->returnValue($rules));

        // 2. Set fixtures, provide tested code isolation
        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);
        $validator->setSkipActionsValidation(true);
        $validator->expects($this->any())->method('_canProcessRule')->will($this->returnValue(true));
        $ruleWithStopFurtherProcessing->setStopRulesProcessing(true);

        // 3. Set expectations
        $callback = function ($rule) use ($ruleThatShouldNotBeRun) {
            /** @var $rule \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject */
            if ($rule->getName() == $ruleThatShouldNotBeRun->getName()) {
                $this->fail('Rule should not be run after applying rule that stops further rules processing');
            }

            return true;
        };
        $validator->expects($this->any())
            ->method('applyRule')
            ->with($this->anything(), $this->callback($callback), $this->anything());

        // 4. Run tested method
        $validator->process($item);

        // 5. Set new expectations
        $validator->expects($this->never())->method('applyRule');   //No rules should be applied further

        // 6. Run tested method again
        $validator->process($item);
    }

    public function testApplyRulesThatAppliedRuleIdsAreCollected()
    {
        $positivePrice = 1;
        $ruleId1 = 123;
        $ruleId2 = 234;
        $expectedRuleIds = array(
            $ruleId1 => $ruleId1,
            $ruleId2 => $ruleId2
        );

        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMock(
            'Magento\SalesRule\Model\Validator',
            array('applyRule', '_getRules', '_canProcessRule', 'setAppliedRuleIds', '__wakeup'),
            array(),
            '',
            false
        );
        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', ['getAddress', '__wakeup'], [], '', false);
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $rule */
        $rule = $this->getMock('Magento\SalesRule\Model\Rule', array('__wakeup'), array(), '', false);
        $rule->setRuleId($ruleId1);
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $rule2 */
        $rule2 = $this->getMock('Magento\SalesRule\Model\Rule', array('__wakeup'), array(), '', false);
        $rule2->setRuleId($ruleId2);
        $rules = array($rule, $rule2,);
        $validator->expects($this->any())->method('_getRules')->will($this->returnValue($rules));

        // 2. Set fixtures, provide tested code isolation
        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);
        $validator->setSkipActionsValidation(true);
        $validator->expects($this->any())->method('_canProcessRule')->will($this->returnValue(true));

        // 3. Set expectations
        $validator->expects($this->once())->method('setAppliedRuleIds')->with($this->anything(), $expectedRuleIds);

        // 4. Run tested method again
        $validator->process($item);
    }

    public function testApplyRule()
    {
        $positivePrice = 1;

        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder('Magento\SalesRule\Model\Validator')
            ->setMethods(array(
                'getDiscountData', 'setDiscountData', '_addDiscountDescription', '_maintainAddressCouponCode',
                '_canProcessRule', 'setAppliedRuleIds', '_getRules', '__wakeup'
            ))
            ->disableOriginalConstructor()
            ->getMock();
        $rule = $this->getMockBuilder('Magento\SalesRule\Model\Rule')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', ['getAddress', '__wakeup'], [], '', false);
        $discountData = $this->getMockBuilder('Magento\SalesRule\Model\Rule\Action\Discount\Data')->getMock();

        // 2.Provide tested code isolation
        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);
        $validator->setSkipActionsValidation(true);
        $validator->expects($this->any())->method('_canProcessRule')->will($this->returnValue(true));
        $validator->expects($this->any())->method('_getRules')->will($this->returnValue(array($rule)));
        $validator->expects($this->any())->method('getDiscountData')->will($this->returnValue($discountData));

        // 3. Set expectations
        $validator->expects($this->any())->method('setDiscountData')->with($discountData);

        // 4. Run tested method again
        $validator->process($item);
    }

    public function testSetAppliedRuleIds()
    {
        $positivePrice = 1;
        $previouslySetRuleIds = array(1, 2, 4);
        $exampleRuleIds = array(1, 2, 3, 5);
        $expectedRuleIds = '1,2,3,5';
        $expectedMergedRuleIds = '1,2,3,4,5';

        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMock('Magento\SalesRule\Model\Validator', ['applyRules', '__wakeup'], [], '', false);
        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', ['getAddress', 'getQuote', '__wakeup'], [], '', false);
        /** @var \Magento\Sales\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMock('Magento\Sales\Model\Quote\Address', ['__wakeup'], [], '', false);
        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->getMock('Magento\Sales\Model\Quote', ['__wakeup'], [], '', false);
        $item->expects($this->any())->method('getAddress')->will($this->returnValue($address));
        $item->expects($this->any())->method('getQuote')->will($this->returnValue($quote));

        // 2. Set fixtures
        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);
        $validator->expects($this->any())->method('applyRules')->will($this->returnValue($exampleRuleIds));
        $address->setAppliedRuleIds($previouslySetRuleIds);
        $quote->setAppliedRuleIds($previouslySetRuleIds);

        // 3. Run tested method
        $validator->process($item);

        // 4. Check expected result
        $this->assertEquals($expectedRuleIds, $item->getAppliedRuleIds());

        $this->assertObjectHasRuleIdsSet($expectedMergedRuleIds, $item->getAddress());
        $this->assertObjectHasRuleIdsSet($expectedMergedRuleIds, $item->getQuote());
    }

    /**
     * @param $expectedMergedRuleIds
     * @param \Magento\Sales\Model\Quote\Address|\Magento\Sales\Model\Quote $object
     * @return $this
     */
    protected function assertObjectHasRuleIdsSet($expectedMergedRuleIds, $object)
    {
        $array = explode(',', $object->getAppliedRuleIds());
        sort($array);
        $this->assertEquals($expectedMergedRuleIds, join(',', $array));

        return $this;
    }
    
    protected function getValidator()
    {
        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\DiscountInterface $calculator */
        $calculator = $this->getMockBuilder('Magento\SalesRule\Model\Rule\Action\Discount\DiscountInterface')
            ->setMethods(array('fixQuantity', 'calculate'))
            ->getMock();

        $discountData = $this->getMockBuilder('Magento\SalesRule\Model\Rule\Action\Discount\Data')
            ->getMock();

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory */
        $calculatorFactory = $this->getMockBuilder('Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $calculator->expects($this->any())->method('fixQuantity');
        $calculator->expects($this->any())->method('calculate')->will($this->returnValue($discountData));
        $calculatorFactory->expects($this->any())->method('create')->will($this->returnValue($calculator));

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $args = $objectManagerHelper->getConstructArguments(
            'Magento\SalesRule\Model\Validator',
            array('calculatorFactory' => $calculatorFactory)
        );

        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder('Magento\SalesRule\Model\Validator')
            ->setMethods(array(
                'getDiscountData', 'setDiscountData', '_addDiscountDescription', '_maintainAddressCouponCode',
                '_getItemQty', '_canProcessRule', 'setAppliedRuleIds', '_getRules', '__wakeup'
            ))
            ->setConstructorArgs($args)
            ->getMock();

        $rule = $this->getMockBuilder('Magento\SalesRule\Model\Rule')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $validator->expects($this->any())->method('_getRules')->will($this->returnValue(array($rule)));


        return $validator;
    }
}
