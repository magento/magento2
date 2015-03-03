<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

class SelectConfigOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage Frontend input type has to be select or radio.
     */
    public function testConstructInvalidFrontendType()
    {
        new SelectConfigOption('test', TextConfigOption::FRONTEND_WIZARD_TEXT, ['a', 'b']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage Select options can't be empty.
     */
    public function testConstructNoOptions()
    {
        new SelectConfigOption('test', SelectConfigOption::FRONTEND_WIZARD_SELECT, []);
    }

    public function testGetFrontendInput()
    {
        $option = new SelectConfigOption('test', SelectConfigOption::FRONTEND_WIZARD_SELECT, ['a', 'b']);
        $this->assertEquals(SelectConfigOption::FRONTEND_WIZARD_SELECT, $option->getFrontendInput());
    }

    public function testGetSelectOptions()
    {
        $option = new SelectConfigOption('test', SelectConfigOption::FRONTEND_WIZARD_SELECT, ['a', 'b']);
        $this->assertEquals(['a', 'b'], $option->getSelectOptions());
    }
}
