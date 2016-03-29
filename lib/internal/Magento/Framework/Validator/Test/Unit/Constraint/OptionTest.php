<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit\Constraint;

/**
 * Test case for \Magento\Framework\Validator\Constraint\Option
 */
class OptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getValue
     */
    public function testGetValue()
    {
        $expected = 'test_value';
        $option = new \Magento\Framework\Validator\Constraint\Option($expected);
        $this->assertEquals($expected, $option->getValue());
    }
}
