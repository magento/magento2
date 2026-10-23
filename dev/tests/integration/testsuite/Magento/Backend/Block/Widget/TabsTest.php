<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class TabsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testAddTab()
    {
        /** @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Widget\Instance::class
        );
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('current_widget_instance', $widgetInstance);

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Backend\Block\Widget\Tabs */
        $block = $layout->createBlock(\Magento\Backend\Block\Widget\Tabs::class, 'block');
        $layout->addBlock(\Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main::class, 'child_tab', 'block');
        $block->addTab('tab_id', 'child_tab');

        $this->assertEquals(['tab_id'], $block->getTabsIds());
    }

    #[AppArea('adminhtml'), AppIsolation(true)]
    public function testActiveTabFromRequestTargetsTheOnlyElementWithItsId(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(RequestInterface::class)->setParam('active_tab', 'second');
        /** @var Tabs $block */
        $block = $objectManager->get(LayoutInterface::class)->createBlock(Tabs::class, 'tabs_block');
        $block->setId('unique_tabs');
        $block->addTab('first', ['label' => 'First', 'content' => 'First content']);
        $block->addTab('second', ['label' => 'Second', 'content' => 'Second content']);

        $html = $block->toHtml();

        $this->assertStringContainsString("active: 'unique_tabs_second'", $html);
        $document = new \DOMDocument();
        $document->loadHTML('<html><body>' . $html . '</body></html>', LIBXML_NOERROR);
        $xpath = new \DOMXPath($document);
        foreach (['unique_tabs_first', 'unique_tabs_second'] as $tabId) {
            $elements = $xpath->query('//*[@id="' . $tabId . '"]');
            $this->assertSame(1, $elements->length, sprintf('Element id "%s" must be unique.', $tabId));
            $this->assertSame('a', $elements->item(0)->nodeName);
        }
    }
}
