<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultSenderWithCurrentCustomer()
    {
        /** Preconditions */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fixtureCustomerId = 1;
        /** @var \Magento\Backend\Model\Session\Quote $backendQuoteSession */
        $backendQuoteSession = $objectManager->get(\Magento\Backend\Model\Session\Quote::class);
        $backendQuoteSession->setCustomerId($fixtureCustomerId);
        /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage\Form $block */
        $block = $objectManager->create(\Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage\Form::class);
        $block->setEntity(new \Magento\Framework\DataObject());

        /** SUT execution and assertions */
        $this->assertEquals('John Smith', $block->getDefaultSender(), 'Sender name is invalid.');
    }
}
