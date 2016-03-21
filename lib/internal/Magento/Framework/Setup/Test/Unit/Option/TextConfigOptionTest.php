<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Option;

use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;

class TextConfigOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Frontend input type has to be 'text', 'textarea' or 'password'.
     */
    public function testConstructInvalidFrontendType()
    {
        new TextConfigOption('test', SelectConfigOption::FRONTEND_WIZARD_SELECT, 'path/to/value');
    }

    public function testGetFrontendType()
    {
        $option = new TextConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, 'path/to/value');
        $this->assertEquals(TextConfigOption::FRONTEND_WIZARD_TEXT, $option->getFrontendType());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage must be a string
     */
    public function testValidateException()
    {
        $option = new TextConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, 'path/to/value');
        $option->validate(1);
    }
}
