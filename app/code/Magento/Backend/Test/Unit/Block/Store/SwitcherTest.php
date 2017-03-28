<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Store;

class SwitcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Store\Switcher
     */
    private $switcherBlock;

    private $storeManagerMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
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
        $websiteMock =  $this->getMock(\Magento\Store\Model\Website::class, [], [], '', false);
        $websites = [0 => $websiteMock, 1 => $websiteMock];
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->will($this->returnValue($websites));
        $this->assertEquals($websites, $this->switcherBlock->getWebsites());
    }

    public function testGetWebsitesIfSetWebsiteIds()
    {
        $websiteMock =  $this->getMock(\Magento\Store\Model\Website::class, [], [], '', false);
        $websites = [0 => $websiteMock, 1 => $websiteMock];
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->will($this->returnValue($websites));

        $this->switcherBlock->setWebsiteIds([1]);
        $expected = [1 => $websiteMock];
        $this->assertEquals($expected, $this->switcherBlock->getWebsites());
    }
}
