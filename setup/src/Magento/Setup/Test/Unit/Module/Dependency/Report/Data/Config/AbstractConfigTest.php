<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Data\Config;

class AbstractConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetModules()
    {
        $modules = ['foo', 'baz', 'bar'];

        /** @var \Magento\Setup\Module\Dependency\Report\Data\Config\AbstractConfig $config */
        $config = $this->getMockForAbstractClass(
            'Magento\Setup\Module\Dependency\Report\Data\Config\AbstractConfig',
            ['modules' => $modules]
        );

        $this->assertEquals($modules, $config->getModules());
    }
}
