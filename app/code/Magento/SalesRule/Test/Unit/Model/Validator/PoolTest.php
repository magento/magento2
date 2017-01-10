<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Validator;

/**
 * Test Class PoolTest
 */
class PoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Validator\Pool;
     */
    protected $pool;

    /**
     * @var array
     */
    protected $validators = [];

    protected function setUp()
    {
        $this->validators = ['discount' => ['validator1', 'validator2']];
        $this->pool = new \Magento\SalesRule\Model\Validator\Pool($this->validators);
    }

    public function testGetValidators()
    {
        $this->assertContains($this->validators['discount'][0], $this->pool->getValidators('discount'));
        $this->assertEquals([], $this->pool->getValidators('fake'));
    }
}
