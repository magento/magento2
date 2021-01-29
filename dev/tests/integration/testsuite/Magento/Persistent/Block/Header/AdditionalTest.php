<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Block\Header;

/**
 * @magentoDataFixture Magento/Persistent/_files/persistent.php
 */
class AdditionalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Block\Header\Additional
     */
    protected $_block;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSessionHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Persistent\Helper\Session $persistentSessionHelper */
        $this->_persistentSessionHelper = $this->_objectManager->create(\Magento\Persistent\Helper\Session::class);

        $this->_customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);

        $this->_block = $this->_objectManager->create(\Magento\Persistent\Block\Header\Additional::class);
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
        $translation = __('Not you?');

        $this->assertStringContainsString('<a href="' . $this->_block->getHref() . '">' . $translation . '</a>', $this->_block->toHtml());
        $this->_customerSession->logout();
    }
}
