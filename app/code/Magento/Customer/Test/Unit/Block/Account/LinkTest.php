<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Account;

use Magento\Customer\Block\Account\Link;
use Magento\Customer\Model\Url;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    public function testGetHref()
    {
        $objectManager = new ObjectManager($this);
        $helper = $this->getMockBuilder(
            Url::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['getAccountUrl']
            )->getMock();
        $layout = $this->getMockBuilder(
            Layout::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['helper']
            )->getMock();

        $block = $objectManager->getObject(
            Link::class,
            ['layout' => $layout, 'customerUrl' => $helper]
        );
        $helper->expects($this->any())->method('getAccountUrl')->willReturn('account url');

        $this->assertEquals('account url', $block->getHref());
    }
}
