<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Option;

use Magento\Framework\Setup\Option\MultiSelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;

class MultiSelectConfigOptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     */
    public function testConstructInvalidFrontendType()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Frontend input type has to be \'multiselect\', \'textarea\' or \'checkbox\'.');

        new MultiSelectConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, ['a', 'b'], 'path/to/value');
    }

    /**
     */
    public function testConstructNoOptions()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Select options can\'t be empty.');

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

    /**
     */
    public function testValidateException()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Value specified for');

        $option = new MultiSelectConfigOption(
            'test',
            MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT,
            ['a', 'b'],
            'path/to/value'
        );
        $option->validate(['c', 'd']);
    }
}
