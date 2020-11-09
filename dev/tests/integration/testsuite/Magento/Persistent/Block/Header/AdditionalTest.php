<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Block\Header;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Persistent\Helper\Session as SessionHelper;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixture Magento/Persistent/_files/persistent.php
 */
class AdditionalTest extends TestCase
{
    /**
     * @var Additional
     */
    protected $_block;

    /**
     * @var SessionHelper
     */
    protected $_persistentSessionHelper;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = Bootstrap::getObjectManager();

        /** @var Session $persistentSessionHelper */
        $this->_persistentSessionHelper = $this->_objectManager->create(Session::class);

        $this->_customerSession = $this->_objectManager->get(Session::class);

        $this->_block = $this->_objectManager->create(Additional::class);
    }

    /**
     * @magentoConfigFixture current_store persistent/options/customer 1
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_default 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        $this->_customerSession->loginById(1);
        $this->assertStringContainsString($this->_block->getHref(), $this->_block->toHtml());
        $this->_customerSession->logout();
    }
}
