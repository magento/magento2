<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Price;

use Magento\Framework\Pricing\Price\Pool;
use PHPUnit\Framework\TestCase;

/**
 * Test for Pool
 */
class PoolTest extends TestCase
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var array
     */
    protected $prices;

    /**
     * @var array
     */
    protected $target;

    /**
     * \Iterator
     */
    protected $targetPool;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->prices = [
            'regular_price' => 'RegularPrice',
            'special_price' => 'SpecialPrice',
        ];
        $this->target = [
            'regular_price' => 'TargetRegularPrice',
        ];
        $this->targetPool = new Pool($this->target);
        $this->pool = new Pool($this->prices, $this->targetPool);
    }

    /**
     * test mergedConfiguration
     */
    public function testMergedConfiguration()
    {
        $expected = new Pool([
            'regular_price' => 'RegularPrice',
            'special_price' => 'SpecialPrice',
        ]);
        $this->assertEquals($expected, $this->pool);
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $this->assertEquals('RegularPrice', $this->pool->get('regular_price'));
        $this->assertEquals('SpecialPrice', $this->pool->get('special_price'));
    }

    /**
     * Test abilities of ArrayAccess interface
     */
    public function testArrayAccess()
    {
        $this->assertEquals('RegularPrice', $this->pool['regular_price']);
        $this->assertEquals('SpecialPrice', $this->pool['special_price']);
        $this->pool['fake_price'] = 'FakePrice';
        $this->assertEquals('FakePrice', $this->pool['fake_price']);
        $this->assertArrayHasKey('fake_price', $this->pool);
        unset($this->pool['fake_price']);
        $this->assertArrayNotHasKey('fake_price', $this->pool);
        $this->assertNull($this->pool['fake_price']);
    }

    /**
     * Test abilities of Iterator interface
     */
    public function testIterator()
    {
        foreach ($this->pool as $code => $class) {
            $this->assertEquals($this->pool[$code], $class);
        }
    }
}
