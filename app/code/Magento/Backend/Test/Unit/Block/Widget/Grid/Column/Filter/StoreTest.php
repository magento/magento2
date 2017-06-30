<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Backend\Block\Widget\Grid\Column\Filter\Store */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\DB\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;

    /** @var \Magento\Store\Model\System\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $store;

    protected function setUp()
    {
        $this->context = $this->getMock(\Magento\Backend\Block\Context::class, [], [], '', false);
        $this->helper = $this->getMock(\Magento\Framework\DB\Helper::class, [], [], '', false);
        $this->store = $this->getMock(\Magento\Store\Model\System\Store::class, [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->store = $this->objectManagerHelper->getObject(
            \Magento\Backend\Block\Widget\Grid\Column\Filter\Store::class,
            [
                'context' => $this->context,
                'resourceHelper' => $this->helper,
                'systemStore' => $this->store
            ]
        );
    }

    /**
     * @param null|array $expectedCondition
     * @param null|int|string $value
     * @dataProvider getConditionDataProvider
     */
    public function testGetCondition($expectedCondition, $value)
    {
        $this->store->setValue($value);
        $this->assertSame($expectedCondition, $this->store->getCondition());
    }

    /**
     * @return array
     */
    public function getConditionDataProvider()
    {
        return [
            [null, null],
            [null, \Magento\Backend\Block\Widget\Grid\Column\Filter\Store::ALL_STORE_VIEWS],
            [['eq' => 1], 1],
            [['null' => true], '_deleted_'],
        ];
    }
}
