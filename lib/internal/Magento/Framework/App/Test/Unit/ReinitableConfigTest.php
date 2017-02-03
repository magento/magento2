<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\App\Test\Unit;

class ReinitableConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testReinit()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $scopePool = $this->getMock('\Magento\Framework\App\Config\ScopePool', ['clean'], [], '', false);
        $scopePool->expects($this->once())->method('clean');
        /** @var \Magento\Framework\App\ReinitableConfig $config */
        $config = $helper->getObject('Magento\Framework\App\ReinitableConfig', ['scopePool' => $scopePool]);
        $this->assertInstanceOf('\Magento\Framework\App\Config\ReinitableConfigInterface', $config->reinit());
    }
}
