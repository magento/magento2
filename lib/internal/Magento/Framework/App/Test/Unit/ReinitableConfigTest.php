<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

class ReinitableConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testReinit()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $scopePool = $this->getMock(\Magento\Framework\App\Config\ScopePool::class, ['clean'], [], '', false);
        $scopePool->expects($this->once())->method('clean');
        /** @var \Magento\Framework\App\ReinitableConfig $config */
        $config = $helper->getObject(\Magento\Framework\App\ReinitableConfig::class, ['scopePool' => $scopePool]);
        $this->assertInstanceOf(\Magento\Framework\App\Config\ReinitableConfigInterface::class, $config->reinit());
    }
}
