<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testGetAdapterName()
    {
        /** @var Config $config */
        $config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Image\Adapter\Config::class);
        $this->assertEquals(\Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_GD2, $config->getAdapterAlias());
    }
}
