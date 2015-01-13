<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    public function testGetHref()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $helper = $this->getMockBuilder(
            'Magento\Customer\Model\Url'
        )->disableOriginalConstructor()->setMethods(
            ['getAccountUrl']
        )->getMock();
        $layout = $this->getMockBuilder(
            'Magento\Framework\View\Layout'
        )->disableOriginalConstructor()->setMethods(
            ['helper']
        )->getMock();

        $block = $objectManager->getObject(
            'Magento\Customer\Block\Account\Link',
            ['layout' => $layout, 'customerUrl' => $helper]
        );
        $helper->expects($this->any())->method('getAccountUrl')->will($this->returnValue('account url'));

        $this->assertEquals('account url', $block->getHref());
    }
}
