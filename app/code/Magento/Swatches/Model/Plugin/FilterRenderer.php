<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Swatches\Model\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Swatches\ViewModel\Product\Renderer\Configurable as ConfigurableViewModel;

class FilterRenderer
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * Path to RenderLayered Block
     *
     * @var string
     */
    protected $block = \Magento\Swatches\Block\LayeredNavigation\RenderLayered::class;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
     * @var ConfigurableViewModel
     */
    private $configurableViewModel;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param ConfigurableViewModel|null $configurableViewModel
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Swatches\Helper\Data $swatchHelper,
        ?ConfigurableViewModel $configurableViewModel = null
    ) {
        $this->layout = $layout;
        $this->swatchHelper = $swatchHelper;
        $this->configurableViewModel = $configurableViewModel
            ?? ObjectManager::getInstance()->get(ConfigurableViewModel::class);
    }

    /**
     * Render swatch filters with the swatch block
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundRender(
        \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
    ) {
        if ($filter->hasAttributeModel()) {
            if ($this->swatchHelper->isSwatchAttribute($filter->getAttributeModel())) {
                return $this->layout
                    ->createBlock($this->block)
                    ->setSwatchFilter($filter)
                    ->setData('configurable_view_model', $this->configurableViewModel)
                    ->toHtml();
            }
        }
        return $proceed($filter);
    }
}
