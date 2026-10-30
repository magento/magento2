<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var Info
     */
    private $infoBlock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Mock JsonHelper for ObjectManager
        $jsonHelperMock = $this->getMockBuilder(JsonHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock ObjectManager to avoid "ObjectManager isn't initialized" error
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->objectManagerMock->method('get')
            ->willReturn($jsonHelperMock);
        
        AppObjectManager::setInstance($this->objectManagerMock);

        $this->infoBlock = $this->objectManager->getObject(
            Info::class
        );
    }

    public function testGetTabLabelAndTitle(): void
    {
        $tabString = 'Integration Info';
        $this->assertEquals($tabString, $this->infoBlock->getTabLabel());
        $this->assertEquals($tabString, $this->infoBlock->getTabTitle());
    }

    public function testCanShowTab(): void
    {
        $this->assertTrue($this->infoBlock->canShowTab());
    }

    public function testIsHidden(): void
    {
        $this->assertFalse($this->infoBlock->isHidden());
    }
}
