<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Validator\Constraint;

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
