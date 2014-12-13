<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
