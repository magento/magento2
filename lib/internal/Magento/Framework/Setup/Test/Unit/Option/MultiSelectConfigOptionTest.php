<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Option;

use Magento\Framework\Setup\Option\MultiSelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use PHPUnit\Framework\TestCase;

class MultiSelectConfigOptionTest extends TestCase
{
    public function testConstructInvalidFrontendType()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Frontend input type has to be \'multiselect\', \'textarea\' or \'checkbox\'.');
        new MultiSelectConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, ['a', 'b'], 'path/to/value');
    }

    public function testConstructNoOptions()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Select options can\'t be empty.');
        new MultiSelectConfigOption('test', MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT, [], 'path/to/value');
    }

    public function testGetFrontendType()
    {
        $option = new MultiSelectConfigOption(
            'test',
            MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT,
            ['a', 'b'],
            'path/to/value'
        );
        $this->assertEquals(MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT, $option->getFrontendType());
    }

    public function testGetSelectOptions()
    {
        $option = new MultiSelectConfigOption(
            'test',
            MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT,
            ['a', 'b'],
            'path/to/value'
        );
        $this->assertEquals(['a', 'b'], $option->getSelectOptions());
    }

    public function testValidateException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Value specified for');
        $option = new MultiSelectConfigOption(
            'test',
            MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT,
            ['a', 'b'],
            'path/to/value'
        );
        $option->validate(['c', 'd']);
    }
}
