<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Escaper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Widget\Model\Widget\Instance;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class LayoutTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Layout
     */
    private $block;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->block = $this->objectManager->get(LayoutInterface::class)
            ->createBlock(
                Layout::class,
                '',
                [
                    'data' => [
                        'widget_instance' => $this->objectManager->create(Instance::class),
                    ],
                ]
            );
        $this->block->setLayout($this->objectManager->get(LayoutInterface::class));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetLayoutsChooser()
    {
        $this->objectManager->get(State::class)
            ->setAreaCode(Area::AREA_FRONTEND);
        $this->objectManager->get(DesignInterface::class)
            ->setDefaultDesignTheme();

        $actualHtml = $this->block->getLayoutsChooser();
        $this->assertStringStartsWith('<select ', $actualHtml);
        $this->assertStringEndsWith('</select>', $actualHtml);
        $this->assertStringContainsString('id="layout_handle"', $actualHtml);
        $optionCount = substr_count($actualHtml, '<option ');
        $this->assertGreaterThan(1, $optionCount, 'HTML select tag must provide options to choose from.');
        $this->assertEquals($optionCount, substr_count($actualHtml, '</option>'));
    }

    /**
     * Check that escapeUrl called from template
     *
     * @return void
     */
    public function testToHtml(): void
    {
        $escaperMock = $this->createMock(Escaper::class);
        $this->objectManager->addSharedInstance($escaperMock, Escaper::class);

        $escaperMock->expects($this->atLeast(6))
            ->method('escapeUrl');

        $this->block->toHtml();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(Escaper::class);
    }
}
