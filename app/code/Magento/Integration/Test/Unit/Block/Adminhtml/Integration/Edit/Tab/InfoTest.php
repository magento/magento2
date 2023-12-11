<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
 */
class InfoTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
     */
    private $infoBlock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->infoBlock = $this->objectManager->getObject(
            Info::class
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
