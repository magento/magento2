<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Model\Layout\Merge as LayoutProcessor;
use Magento\Framework\View\Model\Layout\MergeFactory as LayoutProcessorFactory;

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
        if (!$product->getSku()) {
            return [];
        }

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
     * Extract custom layout attribute value.
     *
     * @param ProductInterface $product
     * @return mixed
     */
    private function extractAttributeValue(ProductInterface $product)
    {
        if ($product instanceof Product && !$product->hasData(ProductInterface::CUSTOM_ATTRIBUTES)) {
            return $product->getData('custom_layout_update_file');
        }
        if ($attr = $product->getCustomAttribute('custom_layout_update_file')) {
            return $attr->getValue();
        }

        return null;
    }

    /**
     * Extract selected custom layout settings.
     *
     * If no update is selected none will apply.
     *
     * @param ProductInterface $product
     * @param DataObject $intoSettings
     * @return void
     */
    public function extractCustomSettings(ProductInterface $product, DataObject $intoSettings): void
    {
        if ($product->getSku() && $value = $this->extractAttributeValue($product)) {
            $handles = $intoSettings->getPageLayoutHandles() ?? [];
            $handles = array_merge_recursive(
                $handles,
                ['selectable' => $this->sanitizeSku($product) . '_' . $value]
            );
            $intoSettings->setPageLayoutHandles($handles);
        }
    }
}
