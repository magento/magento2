<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Option;

use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use PHPUnit\Framework\TestCase;

class SelectConfigOptionTest extends TestCase
{
    public function testConstructInvalidFrontendType()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Frontend input type has to be \'select\' or \'radio\'.');
        new SelectConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, ['a', 'b'], 'path/to/value');
    }

    public function testConstructNoOptions()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Select options can\'t be empty.');
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

    public function testValidateException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Value specified for');
        $option = new SelectConfigOption(
            'test',
            SelectConfigOption::FRONTEND_WIZARD_SELECT,
            ['a', 'b'],
            'path/to/value'
        );
        $option->validate('c');
    }
}
