<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Option;

use Magento\Framework\Setup\Option\FlagConfigOption;
use PHPUnit\Framework\TestCase;

class FlagConfigOptionTest extends TestCase
{
    public function testGetFrontendType()
    {
        $option = new FlagConfigOption('test', FlagConfigOption::FRONTEND_WIZARD_FLAG, 'path/to/value');
        $this->assertEquals(FlagConfigOption::FRONTEND_WIZARD_FLAG, $option->getFrontendType());
    }
}
