<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Constraint;

use Magento\Framework\Validator\Constraint\Option;
use PHPUnit\Framework\TestCase;

/**
 * Test case for \Magento\Framework\Validator\Constraint\Option
 */
class OptionTest extends TestCase
{
    /**
     * Test getValue
     */
    public function testGetValue()
    {
        $expected = 'test_value';
        $option = new Option($expected);
        $this->assertEquals($expected, $option->getValue());
    }
}
