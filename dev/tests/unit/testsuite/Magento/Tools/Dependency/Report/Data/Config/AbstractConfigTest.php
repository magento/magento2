<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Dependency\Report\Data\Config;

class AbstractConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetModules()
    {
        $modules = ['foo', 'baz', 'bar'];

        /** @var \Magento\Tools\Dependency\Report\Data\Config\AbstractConfig $config */
        $config = $this->getMockForAbstractClass(
            'Magento\Tools\Dependency\Report\Data\Config\AbstractConfig',
            ['modules' => $modules]
        );

        $this->assertEquals($modules, $config->getModules());
    }
}
