<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account\Dashboard;

class NewsletterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Newsletter block under test.
     *
     * @var Newsletter
     */
    protected $block;

    /**
     * Session model.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Execute per test initialization.
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $this->block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Account\Dashboard\Newsletter',
            '',
            ['customerSession' => $this->customerSession]
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetSubscriptionObject()
    {
        $this->customerSession->setCustomerId(1);

        $subscriber = $this->block->getSubscriptionObject();
        $this->assertInstanceOf('Magento\Newsletter\Model\Subscriber', $subscriber);
        $this->assertFalse($subscriber->isSubscribed());
    }
}
