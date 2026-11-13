<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\View\Layout;

use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
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
#[AppArea('frontend'), AppIsolation(true)]
class PageLayoutSidebarAdditionalTest extends TestCase
{
    /**
     * @param string $pageLayoutHandle
     * @return void
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
     */
    public function testSidebarAdditionalIsMovedIntoWrapperOnTwoColumnLayout(): void
    {
        $layout = $this->createLayout(['2columns-left']);

        $this->assertSame(
            'div.sidebar.additional',
            $layout->getParentName('sidebar.additional'),
            'On a two-column layout "sidebar.additional" must be nested in the "div.sidebar.additional" wrapper.'
        );
    }

    /**
     * On single-column page layouts the container must not render its children, so blocks attached to it
     * in default.xml (compare, reorder, wishlist) do not start appearing below the main column.
     *
     * @return void
     */
    public function testSidebarAdditionalIsNotRenderedOnOneColumnLayout(): void
    {
        $layout = $this->createLayout(['default', '1column']);

        $this->assertNotEmpty(
            $layout->getChildNames('sidebar.additional'),
            'Blocks from default.xml are expected to be attached to "sidebar.additional".'
        );
        $this->assertSame('', $layout->renderElement('sidebar.additional'));
    }

    /**
     * @return void
     */
    public function testSidebarAdditionalIsRenderedOnTwoColumnLayout(): void
    {
        $layout = $this->createLayout(['default', '2columns-left']);

        $this->assertNotSame('', trim($layout->renderElement('sidebar.additional')));
    }

    /**
     * @param string[] $handles
     * @return LayoutInterface
     */
    private function createLayout(array $handles): LayoutInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(LayoutCache::class)->clean();

        /** @var LayoutInterface $layout */
        $layout = $objectManager->get(LayoutFactory::class)->create();
        $layout->getUpdate()->load($handles);
        $layout->generateXml();
        $layout->generateElements();

        return $layout;
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
            'checkout' => ['checkout'],
            'empty' => ['empty'],
        ];
    }
}
