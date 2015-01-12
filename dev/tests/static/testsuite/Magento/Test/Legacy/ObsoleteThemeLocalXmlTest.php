<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Legacy tests to find themes non-modular local.xml files declaration
 */
namespace Magento\Test\Legacy;

class ObsoleteThemeLocalXmlTest extends \PHPUnit_Framework_TestCase
{
    public function testLocalXmlFilesAbsent()
    {
        $area = '*';
        $package = '*';
        $theme = '*';
        $this->assertEmpty(
            glob(
                \Magento\Framework\Test\Utility\Files::init()->getPathToSource() .
                "/app/design/{$area}/{$package}/{$theme}/local.xml"
            )
        );
    }
}
