<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Integration\Edit\Tab;

/**
 * Test class for \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
     */
    private $infoBlock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->infoBlock = $this->objectManager->getObject(
            \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info::class
        );
    }

    public function testGetTabLabelAndTitle()
    {
        $tabString = 'Integration Info';
        $this->assertEquals($tabString, $this->infoBlock->getTabLabel());
        $this->assertEquals($tabString, $this->infoBlock->getTabTitle());
    }

    public function testCanShowTab()
    {
        $this->assertTrue($this->infoBlock->canShowTab());
    }

    public function testIsHidden()
    {
        $this->assertFalse($this->infoBlock->isHidden());
    }
}
