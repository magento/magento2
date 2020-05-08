<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Validator;

use Magento\SalesRule\Model\Validator\Pool;
use PHPUnit\Framework\TestCase;

/**
 * Test Class PoolTest
 */
class PoolTest extends TestCase
{
    /**
     * @var Pool ;
     */
    protected $pool;

    /**
     * @var array
     */
    protected $validators = [];

    protected function setUp(): void
    {
        $this->validators = ['discount' => ['validator1', 'validator2']];
        $this->pool = new Pool($this->validators);
    }

    public function testGetValidators()
    {
        $this->assertContains($this->validators['discount'][0], $this->pool->getValidators('discount'));
        $this->assertEquals([], $this->pool->getValidators('fake'));
    }
}
