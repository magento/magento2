<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Store;

class SwitcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Store\Switcher
     */
    private $switcherBlock;

    private $storeManagerMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectHelper->getObject(
            \Magento\Backend\Block\Template\Context::class,
            [
                'storeManager' => $this->storeManagerMock,
            ]
        );

        $this->switcherBlock = $objectHelper->getObject(
            \Magento\Backend\Block\Store\Switcher::class,
            ['context' => $context]
        );
    }

    public function testGetWebsites()
    {
        $websiteMock =  $this->createMock(\Magento\Store\Model\Website::class);
        $websites = [0 => $websiteMock, 1 => $websiteMock];
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn($websites);
        $this->assertEquals($websites, $this->switcherBlock->getWebsites());
    }

    public function testGetWebsitesIfSetWebsiteIds()
    {
        $websiteMock =  $this->createMock(\Magento\Store\Model\Website::class);
        $websites = [0 => $websiteMock, 1 => $websiteMock];
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn($websites);

        $this->switcherBlock->setWebsiteIds([1]);
        $expected = [1 => $websiteMock];
        $this->assertEquals($expected, $this->switcherBlock->getWebsites());
    }
}
