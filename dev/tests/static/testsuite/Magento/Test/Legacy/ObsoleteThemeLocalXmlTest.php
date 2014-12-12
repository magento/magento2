<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
