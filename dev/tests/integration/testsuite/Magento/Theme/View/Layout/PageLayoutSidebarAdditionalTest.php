<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\View\Layout;

use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that the "sidebar.additional" container is present in every frontend page layout,
 * so blocks declared against it (compare, reorder, wishlist, PayPal banners) never produce
 * "Broken reference" log entries on single-column pages.
 *
 * @see https://github.com/magento/magento2/issues/36806
 */
class PageLayoutSidebarAdditionalTest extends TestCase
{
    /**
     * @param string $pageLayoutHandle
     * @return void
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    #[DataProvider('pageLayoutHandlesDataProvider')]
    public function testSidebarAdditionalContainerExists(string $pageLayoutHandle): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(LayoutCache::class)->clean();

        /** @var LayoutInterface $layout */
        $layout = $objectManager->get(LayoutFactory::class)->create();
        $layout->getUpdate()->load([$pageLayoutHandle]);
        $layout->generateXml();

        $this->assertNotEmpty(
            $layout->getXpath('//container[@name="sidebar.additional"]'),
            sprintf('The "sidebar.additional" container must exist in the "%s" page layout.', $pageLayoutHandle)
        );
    }

    /**
     * On multi-column page layouts the container must be relocated into the styled
     * "div.sidebar.additional" wrapper, so existing sidebar rendering is preserved.
     *
     * @return void
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testSidebarAdditionalIsMovedIntoWrapperOnTwoColumnLayout(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(LayoutCache::class)->clean();

        /** @var LayoutInterface $layout */
        $layout = $objectManager->get(LayoutFactory::class)->create();
        $layout->getUpdate()->load(['2columns-left']);
        $layout->generateXml();
        $layout->generateElements();

        $this->assertSame(
            'div.sidebar.additional',
            $layout->getParentName('sidebar.additional'),
            'On a two-column layout "sidebar.additional" must be nested in the "div.sidebar.additional" wrapper.'
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function pageLayoutHandlesDataProvider(): array
    {
        return [
            '1column' => ['1column'],
            '2columns-left' => ['2columns-left'],
            '2columns-right' => ['2columns-right'],
            '3columns' => ['3columns'],
        ];
    }
}
