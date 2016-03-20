<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Option;

use Magento\Framework\Setup\Option\FlagConfigOption;

class FlagConfigOptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFrontendType()
    {
        $option = new FlagConfigOption('test', FlagConfigOption::FRONTEND_WIZARD_FLAG, 'path/to/value');
        $this->assertEquals(FlagConfigOption::FRONTEND_WIZARD_FLAG, $option->getFrontendType());
    }
}
