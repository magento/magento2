<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account\Dashboard;

class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Block\Account\Dashboard\Info
     */
    protected $_block;

    protected function setUp(): void
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Account\Dashboard\Info::class
        );
    }

    public function testGetSubscriptionObject()
    {
        $object = $this->_block->getSubscriptionObject();
        $this->assertInstanceOf(\Magento\Newsletter\Model\Subscriber::class, $object);

        $object2 = $this->_block->getSubscriptionObject();
        $this->assertSame($object, $object2);
    }
}
