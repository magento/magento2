<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Data\Config;

use Magento\Setup\Module\Dependency\Report\Data\Config\AbstractConfig;
use PHPUnit\Framework\TestCase;

class AbstractConfigTest extends TestCase
{
    public function testGetModules()
    {
        $modules = ['foo', 'baz', 'bar'];

        /** @var AbstractConfig $config */
        $config = $this->getMockForAbstractClass(
            AbstractConfig::class,
            ['modules' => $modules]
        );

        $this->assertEquals($modules, $config->getModules());
    }
}
