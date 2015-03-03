<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

class ConfigOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage Unknown frontend input type.
     */
    public function testConstructInvalidFrontendType()
    {
        new ConfigOption('test', 'invalid_type');
    }

    public function testGetFrontendInput()
    {
        $option = new ConfigOption('test', ConfigOption::FRONTEND_WIZARD_TEXT);
        $this->assertEquals(ConfigOption::FRONTEND_WIZARD_TEXT, $option->getFrontendInput());
    }

    public function testGetSelectOptions()
    {
        $option = new ConfigOption('test', ConfigOption::FRONTEND_WIZARD_SELECT, '', ['option1', 'option2', 'option3']);
        $this->assertEquals(['option1', 'option2', 'option3'], $option->getSelectOptions());
    }
}
