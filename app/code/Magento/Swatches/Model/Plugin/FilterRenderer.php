<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Model\Plugin;

use Closure;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutInterface;
use Magento\LayeredNavigation\Block\Navigation\FilterRenderer as Subject;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\ViewModel\Product\Renderer\Configurable as ConfigurableViewModel;

class FilterRenderer
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Data
     */
    private $swatchHelper;

    /**
     * @var ConfigurableViewModel|null
     */
    private $configurableViewModel;

    /**
     * @var string
     */
    private $block = RenderLayered::class;

    /**
     * @param LayoutInterface $layout
     * @param Data $swatchHelper
     * @param ConfigurableViewModel $configurableViewModel
     */
    public function __construct(
        LayoutInterface $layout,
        Data $swatchHelper,
        ConfigurableViewModel $configurableViewModel
    ) {
        $this->layout = $layout;
        $this->swatchHelper = $swatchHelper;
        $this->configurableViewModel = $configurableViewModel;
    }

    /**
     * Override block rendered for swatch attribute in layered navigation
     *
     * @param Subject $subject
     * @param Closure $proceed
     * @param FilterInterface $filter
     *
     * @return mixed
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRender(
        Subject $subject,
        Closure $proceed,
        FilterInterface $filter
    ) {
        if ($filter->hasAttributeModel() && $this->swatchHelper->isSwatchAttribute($filter->getAttributeModel())) {
            return $this->layout
                ->createBlock($this->block)
                ->setSwatchFilter($filter)
                ->setData('configurable_view_model', $this->configurableViewModel)
                ->toHtml();
        }

        return $proceed($filter);
    }
}
