<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ToolkitFramework;

class FixtureSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testUnaccessibleConfig()
    {
        $this->setExpectedException('Exception', 'Fixtures set file `))` is not readable or does not exists.');
        new \Magento\ToolkitFramework\FixtureSet('))');
    }
}
