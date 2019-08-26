<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Area;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Model\Layout\Merge as LayoutProcessor;
use Magento\Framework\View\Model\Layout\MergeFactory as LayoutProcessorFactory;
use Magento\Framework\View\Result\Page as PageLayout;

/**
 * Manage available layout updates for products.
 */
class LayoutUpdateManager
{

    /**
     * @var FlyweightFactory
     */
    private $themeFactory;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * @var LayoutProcessorFactory
     */
    private $layoutProcessorFactory;

    /**
     * @var LayoutProcessor|null
     */
    private $layoutProcessor;

    /**
     * @param FlyweightFactory $themeFactory
     * @param DesignInterface $design
     * @param LayoutProcessorFactory $layoutProcessorFactory
     */
    public function __construct(
        FlyweightFactory $themeFactory,
        DesignInterface $design,
        LayoutProcessorFactory $layoutProcessorFactory
    ) {
        $this->themeFactory = $themeFactory;
        $this->design = $design;
        $this->layoutProcessorFactory = $layoutProcessorFactory;
    }

    /**
     * Adopt product's SKU to be used as layout handle.
     *
     * @param ProductInterface $product
     * @return string
     */
    private function sanitizeSku(ProductInterface $product): string
    {
        return rawurlencode($product->getSku());
    }

    /**
     * Get the processor instance.
     *
     * @return LayoutProcessor
     */
    private function getLayoutProcessor(): LayoutProcessor
    {
        if (!$this->layoutProcessor) {
            $this->layoutProcessor = $this->layoutProcessorFactory->create(
                [
                    'theme' => $this->themeFactory->create(
                        $this->design->getConfigurationDesignTheme(Area::AREA_FRONTEND)
                    )
                ]
            );
            $this->themeFactory = null;
            $this->design = null;
        }

        return $this->layoutProcessor;
    }

    /**
     * Fetch list of available files/handles for the product.
     *
     * @param ProductInterface $product
     * @return string[]
     */
    public function fetchAvailableFiles(ProductInterface $product): array
    {
        $identifier = $this->sanitizeSku($product);
        $handles = $this->getLayoutProcessor()->getAvailableHandles();

        return array_filter(
            array_map(
                function (string $handle) use ($identifier) : ?string {
                    preg_match(
                        '/^catalog\_product\_view\_selectable\_' .preg_quote($identifier) .'\_([a-z0-9]+)/i',
                        $handle,
                        $selectable
                    );
                    if (!empty($selectable[1])) {
                        return $selectable[1];
                    }

                    return null;
                },
                $handles
            )
        );
    }

    /**
     * Apply selected custom layout updates.
     *
     * If no update is selected none will apply.
     *
     * @param PageLayout $layout
     * @param ProductInterface $product
     * @return void
     */
    public function applyUpdate(PageLayout $layout, ProductInterface $product): void
    {
        if ($attribute = $product->getCustomAttribute('custom_layout_update_file')) {
            $layout->addPageLayoutHandles(
                ['selectable' => $this->sanitizeIdentifier($product) . '_' . $attribute->getValue()]
            );
        }
    }
}
