<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Block\Html\Header;

use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Cache;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
#[Cache('layout', false)]
class CriticalCssTest extends TestCase
{
    private const LUMA_CRITICAL_CSS = '.page-main{flex-grow:1}';

    #[Config('dev/css/use_css_critical_path', 1, 'store', 'default')]
    public function testDefaultHandleRendersThemeCriticalCss(): void
    {
        $this->assertStringContainsString(self::LUMA_CRITICAL_CSS, $this->renderCriticalCss());
    }

    #[Config('dev/css/use_css_critical_path', 1, 'store', 'default')]
    public function testBlockKeepsViewModelForTemplateOverrides(): void
    {
        $block = $this->createCriticalCssBlock();
        $viewModel = $block->getData('criticalCssViewModel');

        $this->assertInstanceOf(Template::class, $block);
        $this->assertInstanceOf(CriticalCss::class, $viewModel);
        $this->assertStringContainsString(self::LUMA_CRITICAL_CSS, $viewModel->getCriticalCssData());
    }

    #[Config('dev/css/use_css_critical_path', 1, 'store', 'default')]
    public function testVirtualTypeViewModelReplacesDefault(): void
    {
        Bootstrap::getObjectManager()->configure([
            'CriticalCssCalendar' => [
                'type' => CriticalCss::class,
                'arguments' => ['filePath' => 'mage/calendar.css'],
            ],
        ]);

        $html = $this->renderCriticalCss(viewModel: 'CriticalCssCalendar');

        $this->assertStringContainsString('.ui-datepicker', $html);
        $this->assertStringNotContainsString(self::LUMA_CRITICAL_CSS, $html);
    }

    #[Config('dev/css/use_css_critical_path', 1, 'store', 'default')]
    public function testLayoutFilePathReplacesDefault(): void
    {
        $html = $this->renderCriticalCss('mage/calendar.css');

        $this->assertStringContainsString('.ui-datepicker', $html);
        $this->assertStringNotContainsString(self::LUMA_CRITICAL_CSS, $html);
    }

    #[Config('dev/css/use_css_critical_path', 1, 'store', 'default')]
    public function testCustomViewModelIgnoresLayoutFilePath(): void
    {
        $block = $this->createCriticalCssBlock('mage/calendar.css');
        $block->setData('criticalCssViewModel', new class implements ArgumentInterface {
            public function getCriticalCssData(): string
            {
                return '.custom-view-model{}';
            }
        });

        $html = $block->toHtml();

        $this->assertStringContainsString('.custom-view-model{}', $html);
        $this->assertStringNotContainsString('.ui-datepicker', $html);
    }

    #[Config('dev/css/use_css_critical_path', 1, 'store', 'default')]
    public function testMissingLayoutFileRendersEmptyStyle(): void
    {
        $this->assertMatchesRegularExpression(
            '#<style type="text/css" data-type="criticalCss">\s*</style>#',
            $this->renderCriticalCss('css/does-not-exist.css')
        );
    }

    #[Config('dev/css/use_css_critical_path', 0, 'store', 'default')]
    public function testDisabledCriticalCssRendersNothing(): void
    {
        $this->assertSame('', $this->renderCriticalCss('mage/calendar.css'));
    }

    private function renderCriticalCss(?string $filePath = null, ?string $viewModel = null): string
    {
        $block = $this->createCriticalCssBlock($filePath, $viewModel);
        return $block ? $block->toHtml() : '';
    }

    private function createCriticalCssBlock(?string $filePath = null, ?string $viewModel = null): ?BlockInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(DesignInterface::class)->setDesignTheme('Magento/luma');

        $arguments = '';
        if ($filePath !== null) {
            $arguments .= '<argument name="file_path" xsi:type="string">' . $filePath . '</argument>';
        }
        if ($viewModel !== null) {
            $arguments .= '<argument name="criticalCssViewModel" xsi:type="object">' . $viewModel . '</argument>';
        }

        /** @var LayoutInterface $layout */
        $layout = $objectManager->create(LayoutInterface::class);
        $layout->getUpdate()->addHandle('default')->load();
        if ($arguments !== '') {
            $layout->getUpdate()->addUpdate(
                '<referenceBlock name="critical_css_block"><arguments>' . $arguments . '</arguments></referenceBlock>'
            );
        }
        $layout->generateXml();
        $layout->generateElements();

        return $layout->getBlock('critical_css_block') ?: null;
    }
}
