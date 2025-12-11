<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Store;
use Magento\Framework\DB\Helper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\System\Store as SystemStore;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /** @var Store */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $context;

    /** @var Helper|MockObject */
    protected $helper;

    /** @var \Magento\Store\Model\System\Store|MockObject */
    protected $store;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->helper = $this->createMock(Helper::class);
        $this->store = $this->createMock(SystemStore::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->store = $this->objectManagerHelper->getObject(
            Store::class,
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
     */
    #[DataProvider('getConditionDataProvider')]
    public function testGetCondition($expectedCondition, $value)
    {
        $this->store->setValue($value);
        $this->assertSame($expectedCondition, $this->store->getCondition());
    }

    /**
     * @return array
     */
    public static function getConditionDataProvider()
    {
        return [
            [null, null],
            [null, Store::ALL_STORE_VIEWS],
            [['eq' => 1], 1],
            [['null' => true], '_deleted_'],
        ];
    }
}
