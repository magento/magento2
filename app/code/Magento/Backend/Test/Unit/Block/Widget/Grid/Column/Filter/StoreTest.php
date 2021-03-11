<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StoreTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Backend\Block\Widget\Grid\Column\Filter\Store */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    /** @var \Magento\Framework\DB\Helper|\PHPUnit\Framework\MockObject\MockObject */
    protected $helper;

    /** @var \Magento\Store\Model\System\Store|\PHPUnit\Framework\MockObject\MockObject */
    protected $store;

    protected function setUp(): void
    {
        $this->context = $this->createMock(\Magento\Backend\Block\Context::class);
        $this->helper = $this->createMock(\Magento\Framework\DB\Helper::class);
        $this->store = $this->createMock(\Magento\Store\Model\System\Store::class);

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
