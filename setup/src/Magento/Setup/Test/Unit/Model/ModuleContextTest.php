<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ModuleContext;
use PHPUnit\Framework\TestCase;

class ModuleContextTest extends TestCase
{
    public function testGetVersion()
    {
        $version = '1.0.1';
        $object = new ModuleContext($version);
        $this->assertSame($version, $object->getVersion());
    }
}
