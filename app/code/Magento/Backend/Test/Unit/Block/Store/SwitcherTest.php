<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Store;

use Magento\Backend\Block\Store\Switcher;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

class SwitcherTest extends TestCase
{
    /**
     * @var Switcher
     */
    private $switcherBlock;

    private $storeManagerMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $objectHelper = new ObjectManager($this);
        $context = $objectHelper->getObject(
            Context::class,
            [
                'storeManager' => $this->storeManagerMock,
            ]
        );

        $this->switcherBlock = $objectHelper->getObject(
            Switcher::class,
            ['context' => $context]
        );
    }

    public function testGetWebsites()
    {
        $websiteMock =  $this->createMock(Website::class);
        $websites = [0 => $websiteMock, 1 => $websiteMock];
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn($websites);
        $this->assertEquals($websites, $this->switcherBlock->getWebsites());
    }

    public function testGetWebsitesIfSetWebsiteIds()
    {
        $websiteMock =  $this->createMock(Website::class);
        $websites = [0 => $websiteMock, 1 => $websiteMock];
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn($websites);

        $this->switcherBlock->setWebsiteIds([1]);
        $expected = [1 => $websiteMock];
        $this->assertEquals($expected, $this->switcherBlock->getWebsites());
    }
}
