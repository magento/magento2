<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Model;

use Magento\TestFramework\Helper\Bootstrap;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigInterface
     */
    private $configModel;

    protected function setUp()
    {
        $this->configModel = Bootstrap::getObjectManager()->create(\Magento\Contact\Model\ConfigInterface::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store contact/contact/enabled 1
     */
    public function testIsEnabled()
    {
        $this->assertTrue($this->configModel->isEnabled());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store contact/contact/enabled 0
     */
    public function testIsNotEnabled()
    {
        $this->assertFalse($this->configModel->isEnabled());
    }
}
