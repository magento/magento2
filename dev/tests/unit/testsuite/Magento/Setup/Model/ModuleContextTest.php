<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class ModuleContextTest extends \PHPUnit_Framework_TestCase
{
    public function testGetVersion()
    {
        $version = '1.0.1';
        $object = new ModuleContext($version);
        $this->assertSame($version, $object->getVersion());
    }
}
