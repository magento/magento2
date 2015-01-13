<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class ReinitableConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testReinit()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $scopePool = $this->getMock('\Magento\Framework\App\Config\ScopePool', ['clean'], [], '', false);
        $scopePool->expects($this->once())->method('clean');
        /** @var \Magento\Core\Model\ReinitableConfig $config */
        $config = $helper->getObject('Magento\Framework\App\ReinitableConfig', ['scopePool' => $scopePool]);
        $this->assertInstanceOf('\Magento\Framework\App\Config\ReinitableConfigInterface', $config->reinit());
    }
}
