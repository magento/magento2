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

class TextConfigOptionTest extends TestCase
{
    public function testConstructInvalidFrontendType()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Frontend input type has to be \'text\', \'textarea\' or \'password\'.');
        new TextConfigOption('test', SelectConfigOption::FRONTEND_WIZARD_SELECT, 'path/to/value');
    }

    public function testGetFrontendType()
    {
        $option = new TextConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, 'path/to/value');
        $this->assertEquals(TextConfigOption::FRONTEND_WIZARD_TEXT, $option->getFrontendType());
    }

    public function testValidateException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('must be a string');
        $option = new TextConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, 'path/to/value');
        $option->validate(1);
    }
}
