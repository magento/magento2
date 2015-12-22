<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Legacy tests to find themes non-modular local.xml files declaration
 */
namespace Magento\Test\Legacy;

use Magento\Framework\Component\ComponentRegistrar;

class ObsoleteThemeLocalXmlTest extends \PHPUnit_Framework_TestCase
{
    public function testLocalXmlFilesAbsent()
    {
        $componentRegistrar = new ComponentRegistrar();
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themeDir) {
            $this->assertEmpty(glob($themeDir . '/local.xml'));
        }
    }
}
