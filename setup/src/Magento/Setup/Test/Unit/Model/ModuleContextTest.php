<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ModuleContext;

class ModuleContextTest extends \PHPUnit_Framework_TestCase
{
    public function testGetVersion()
    {
        $version = '1.0.1';
        $object = new ModuleContext($version);
        $this->assertSame($version, $object->getVersion());
    }
}
