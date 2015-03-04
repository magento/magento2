<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

class MultiSelectConfigOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage Frontend input type has to be select or radio.
     */
    public function testConstructInvalidFrontendType()
    {
        new MultiSelectConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, ['a', 'b']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage Select options can't be empty.
     */
    public function testConstructNoOptions()
    {
        new MultiSelectConfigOption('test', MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT, []);
    }

    public function testGetFrontendType()
    {
        $option = new MultiSelectConfigOption('test', MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT, ['a', 'b']);
        $this->assertEquals(MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT, $option->getFrontendType());
    }

    public function testGetSelectOptions()
    {
        $option = new MultiSelectConfigOption('test', MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT, ['a', 'b']);
        $this->assertEquals(['a', 'b'], $option->getSelectOptions());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage Frontend input type has to be text, textarea or password.
     */
    public function testValidateException()
    {
        $option = new SelectConfigOption('test', SelectConfigOption::FRONTEND_WIZARD_SELECT, ['a', 'b']);
        $option->validate('c');
    }
}
