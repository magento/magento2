<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Helper;

use \Zend\Stdlib\Parameters;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Developer\Helper\Data
     */
    protected $helper = null;

    protected function setUp()
    {
        $this->helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Developer\Helper\Data'
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testIsDevAllowedDefault()
    {
        $this->assertTrue($this->helper->isDevAllowed());
    }

    /**
     * @magentoConfigFixture current_store dev/restrict/allow_ips 192.168.0.1
     * @magentoAppIsolation enabled
     */
    public function testIsDevAllowedTrue()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\TestFramework\Request $request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setServer(new Parameters(['REMOTE_ADDR' => '192.168.0.1']));

        $this->assertTrue($this->helper->isDevAllowed());
    }

    /**
     * @magentoConfigFixture current_store dev/restrict/allow_ips 192.168.0.1
     * @magentoAppIsolation enabled
     */
    public function testIsDevAllowedFalse()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\TestFramework\Request $request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setServer(new Parameters(['REMOTE_ADDR' => '192.168.0.3']));

        $this->assertFalse($this->helper->isDevAllowed());
    }
}
