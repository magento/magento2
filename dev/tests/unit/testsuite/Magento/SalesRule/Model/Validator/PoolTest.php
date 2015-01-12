<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Validator;

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

    public function setUp()
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
