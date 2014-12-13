<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
