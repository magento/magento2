<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Helper;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetHelper()
    {
        $helper = \Magento\TestFramework\Helper\Factory::getHelper(\Magento\TestFramework\Helper\Config::class);
        $this->assertNotEmpty($helper);

        $helperNew = \Magento\TestFramework\Helper\Factory::getHelper(\Magento\TestFramework\Helper\Config::class);
        $this->assertSame($helper, $helperNew, 'Factory must cache instances of helpers.');
    }

    public function testSetHelper()
    {
        $helper = new \stdClass();
        \Magento\TestFramework\Helper\Factory::setHelper(\Magento\TestFramework\Helper\Config::class, $helper);
        $helperGot = \Magento\TestFramework\Helper\Factory::getHelper(\Magento\TestFramework\Helper\Config::class);
        $this->assertSame($helper, $helperGot, 'The helper must be used, when requested again');

        $helperNew = new \stdClass();
        \Magento\TestFramework\Helper\Factory::setHelper(\Magento\TestFramework\Helper\Config::class, $helperNew);
        $helperGot = \Magento\TestFramework\Helper\Factory::getHelper(\Magento\TestFramework\Helper\Config::class);
        $this->assertSame($helperNew, $helperGot, 'The helper must be changed upon new setHelper() method');
    }
}
