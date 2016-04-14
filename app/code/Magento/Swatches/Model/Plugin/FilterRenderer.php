<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

/**
 * Class FilterRenderer
 */
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
    protected $block = 'Magento\Swatches\Block\LayeredNavigation\RenderLayered';

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        $this->layout = $layout;
        $this->swatchHelper = $swatchHelper;
    }

    /**
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
                    ->toHtml();
            }
        }
        return $proceed($filter);
    }
}
