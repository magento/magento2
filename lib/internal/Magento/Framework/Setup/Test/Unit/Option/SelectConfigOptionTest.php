<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Option;

use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;

class SelectConfigOptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Frontend input type has to be 'select' or 'radio'.
     */
    public function testConstructInvalidFrontendType()
    {
        new SelectConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, ['a', 'b'], 'path/to/value');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Select options can't be empty.
     */
    public function testConstructNoOptions()
    {
        new SelectConfigOption('test', SelectConfigOption::FRONTEND_WIZARD_SELECT, [], 'path/to/value');
    }

    public function testGetFrontendType()
    {
        $option = new SelectConfigOption(
            'test',
            SelectConfigOption::FRONTEND_WIZARD_SELECT,
            ['a', 'b'],
            'path/to/value'
        );
        $this->assertEquals(SelectConfigOption::FRONTEND_WIZARD_SELECT, $option->getFrontendType());
    }

    public function testGetSelectOptions()
    {
        $option = new SelectConfigOption(
            'test',
            SelectConfigOption::FRONTEND_WIZARD_SELECT,
            ['a', 'b'],
            'path/to/value'
        );
        $this->assertEquals(['a', 'b'], $option->getSelectOptions());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Value specified for
     */
    public function testValidateException()
    {
        $option = new SelectConfigOption(
            'test',
            SelectConfigOption::FRONTEND_WIZARD_SELECT,
            ['a', 'b'],
            'path/to/value'
        );
        $option->validate('c');
    }
}
