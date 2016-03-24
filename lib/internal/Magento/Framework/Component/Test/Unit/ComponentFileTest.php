<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\ComponentFile;

class ComponentFileTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $type = 'type';
        $name = 'name';
        $path = 'path';
        $component = new ComponentFile($type, $name, $path);
        $this->assertSame($type, $component->getComponentType());
        $this->assertSame($name, $component->getComponentName());
        $this->assertSame($path, $component->getFullPath());
    }
}
