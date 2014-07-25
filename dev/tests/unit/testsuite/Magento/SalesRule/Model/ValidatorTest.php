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

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;


    protected function setUp()
    {
        // @TODO Re-write test according to standards of writing test (e.g do not mock tested class)
        $this->model = $this->getMock(
            'Magento\SalesRule\Model\Validator',
            array('_getRules', '_getItemOriginalPrice', '_getItemBaseOriginalPrice', '__wakeup'),
            array(),
            '',
            false
        );
        $this->model->expects($this->any())->method('_getRules')->will($this->returnValue(array()));
        $this->model->expects($this->any())->method('_getItemOriginalPrice')->will($this->returnValue(1));
        $this->model->expects($this->any())->method('_getItemBaseOriginalPrice')->will($this->returnValue(1));
    }

    /**
     * @return \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteItemMock()
    {
        $fixturePath = __DIR__ . '/_files/';
        $itemDownloadable = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            array('getAddress', '__wakeup'),
            array(),
            '',
            false
        );
        $itemDownloadable->expects($this->any())->method('getAddress')->will($this->returnValue(new \stdClass()));

        $itemSimple = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            array('getAddress', '__wakeup'),
            array(),
            '',
            false
        );
        $itemSimple->expects($this->any())->method('getAddress')->will($this->returnValue(new \stdClass()));

        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $this->getMock('Magento\Sales\Model\Quote', array('hasNominalItems', '__wakeup'), array(), '', false);
        $quote->expects($this->any())->method('hasNominalItems')->will($this->returnValue(false));

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
        $item = $this->getQuoteItemMock();

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

        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMock(
            'Magento\SalesRule\Model\Validator',
            array('__wakeup'),
            array(),
            '',
            false
        );
        $rulesApplier = $this->getMock(
            'Magento\SalesRule\Model\Validator\RulesApplier',
            ['applyRules', '__wakeup'],
            [],
            '',
            false
        );

        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array('__wakeup'), array(), '', false);

        // 2. Set fixtures
        $item->setDiscountCalculationPrice($negativePrice);
        $item->setData('calculation_price', $negativePrice);

        // 3. Set expectations
        $rulesApplier->expects($this->never())->method('applyRules');

        // 4. Run tested method
        $validator->process($item);
    }

    public function testProcessWhenItemPriceIsNegativeDiscountsAreZeroed()
    {
        $negativePrice = -1;
        $nonZeroDiscount = 123;

        // 1. Get mocks
        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMock(
            'Magento\SalesRule\Model\Validator',
            array('__wakeup'),
            array(),
            '',
            false
        );

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

    public function testApplyRulesThatAppliedRuleIdsAreCollected()
    {
        $positivePrice = 1;
        $ruleId1 = 123;
        $ruleId2 = 234;
        $expectedRuleIds = array($ruleId1 => $ruleId1, $ruleId2 => $ruleId2);

        // 1. Get mocks
        $rulesApplier = $this->getMock(
            'Magento\SalesRule\Model\RulesApplier',
            ['applyRules', 'setAppliedRuleIds'],
            [],
            '',
            false
        );
        $context = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $collectionFactory = $this->getMock(
            'Magento\SalesRule\Model\Resource\Rule\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $taxData = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $utility = $this->getMock('Magento\SalesRule\Model\Utility', [], [], '', false);

        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMock(
            'Magento\SalesRule\Model\Validator',
            ['_getRules', '_canProcessRule', '__wakeup'],
            [
                'context' => $context,
                'registry' => $registry,
                'collectionFactory' => $collectionFactory,
                'taxData' => $taxData,
                'utility' => $utility,
                'rulesApplier' => $rulesApplier
            ],
            '',
            true
        );

        /** @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array('getAddress', '__wakeup'), array(), '', false);
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $rule */
        $rule = $this->getMock('Magento\SalesRule\Model\Rule', array('__wakeup'), array(), '', false);
        $rule->setRuleId($ruleId1);
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $rule2 */
        $rule2 = $this->getMock('Magento\SalesRule\Model\Rule', array('__wakeup'), array(), '', false);
        $rule2->setRuleId($ruleId2);
        $rules = array($rule, $rule2);
        $validator->expects($this->any())->method('_getRules')->will($this->returnValue($rules));

        // 2. Set fixtures, provide tested code isolation
        $item->setDiscountCalculationPrice($positivePrice);
        $item->setData('calculation_price', $positivePrice);
        $validator->setSkipActionsValidation(true);

        // 3. Set expectations
        $rulesApplier->expects($this->once())
            ->method('applyRules')
            ->with(
                $this->equalTo($item),
                $this->equalTo($rules),
                $this->anything(),
                $this->anything()
            )
            ->will($this->returnValue($expectedRuleIds));
        $rulesApplier->expects($this->once())->method('setAppliedRuleIds')->with($this->anything(), $expectedRuleIds);

        // 4. Run tested method again
        $validator->process($item);
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
        $calculator = $this->getMockBuilder(
            'Magento\SalesRule\Model\Rule\Action\Discount\DiscountInterface'
        )->setMethods(
            array('fixQuantity', 'calculate')
        )->getMock();

        $discountData = $this->getMockBuilder('Magento\SalesRule\Model\Rule\Action\Discount\Data')->getMock();

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory */
        $calculatorFactory = $this->getMockBuilder(
            'Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $calculator->expects($this->any())->method('fixQuantity');
        $calculator->expects($this->any())->method('calculate')->will($this->returnValue($discountData));
        $calculatorFactory->expects($this->any())->method('create')->will($this->returnValue($calculator));

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $args = $objectManagerHelper->getConstructArguments(
            'Magento\SalesRule\Model\Validator',
            array('calculatorFactory' => $calculatorFactory)
        );

        /** @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(
            'Magento\SalesRule\Model\Validator'
        )->setMethods(
            array(
                'getDiscountData',
                'setDiscountData',
                '_addDiscountDescription',
                '_maintainAddressCouponCode',
                '_getItemQty',
                '_canProcessRule',
                'setAppliedRuleIds',
                '_getRules',
                '__wakeup'
            )
        )->setConstructorArgs(
            $args
        )->getMock();

        $rule = $this->getMockBuilder(
            'Magento\SalesRule\Model\Rule'
        )->disableOriginalConstructor()->setMethods(
            array()
        )->getMock();
        $validator->expects($this->any())->method('_getRules')->will($this->returnValue(array($rule)));


        return $validator;
    }

    public function testInit()
    {
        $websiteId = 1;
        $customerGroupId = 2;
        $couponCode = 'code';

        $ruleCollection = $this->getMockBuilder('Magento\SalesRule\Model\Resource\Rule\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $ruleCollection->expects($this->once())
            ->method('setValidationFilter')
            ->with($websiteId, $customerGroupId, $couponCode)
            ->will($this->returnSelf());
        $ruleCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('is_active', 1)
            ->will($this->returnSelf());
        $ruleCollection->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());

        $ruleCollectionFactoryMock = $this->getMockBuilder('Magento\SalesRule\Model\Resource\Rule\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $ruleCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($ruleCollection));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $model = $helper->getObject(
            'Magento\SalesRule\Model\Validator',
            [
                'collectionFactory' => $ruleCollectionFactoryMock
            ]
        );

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Validator',
            $model->init($websiteId, $customerGroupId, $couponCode)
        );
    }
}
