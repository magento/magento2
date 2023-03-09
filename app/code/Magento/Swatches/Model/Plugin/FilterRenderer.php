<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

use Closure;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutInterface;
use Magento\LayeredNavigation\Block\Navigation\FilterRenderer as NavigationFilterRenderer;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered;
use Magento\Swatches\Helper\Data as SwatchHelper;

class FilterRenderer
{
    /**
     * Path to RenderLayered Block
     *
     * @var string
     */
    protected $block = RenderLayered::class;

    /**
     * @param LayoutInterface $layout
     * @param SwatchHelper $swatchHelper
     */
    public function __construct(
        protected readonly LayoutInterface $layout,
        protected readonly SwatchHelper $swatchHelper
    ) {
    }

    /**
     * If filter has an attribute model and is a swatch add block to html
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param NavigationFilterRenderer $subject
     * @param Closure $proceed
     * @param FilterInterface $filter
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundRender(
        NavigationFilterRenderer $subject,
        Closure $proceed,
        FilterInterface $filter
    ) {
        if ($filter->hasAttributeModel()) {
            if ($this->swatchHelper->isSwatchAttribute($filter->getAttributeModel())) {
                return $this->layout
                    ->createBlock($this->block)
                    ->setSwatchFilter($filter)
                    ->toHtml();
            }
        }
        return $proceed($filter);
    }
}
