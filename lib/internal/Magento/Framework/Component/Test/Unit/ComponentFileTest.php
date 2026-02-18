<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\ComponentFile;
use PHPUnit\Framework\TestCase;

class ComponentFileTest extends TestCase
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
