<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Helper;

use \Laminas\Stdlib\Parameters;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Developer\Helper\Data
     */
    protected $helper = null;

    protected function setUp(): void
    {
        $this->helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Developer\Helper\Data::class
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
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
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
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
        $request->setServer(new Parameters(['REMOTE_ADDR' => '192.168.0.3']));

        $this->assertFalse($this->helper->isDevAllowed());
    }
}
