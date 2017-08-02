<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

/**
 * Class FilterRenderer
 * @since 2.0.0
 */
class FilterRenderer
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    protected $layout;

    /**
     * Path to RenderLayered Block
     *
     * @var string
     * @since 2.0.0
     */
    protected $block = \Magento\Swatches\Block\LayeredNavigation\RenderLayered::class;

    /**
     * @var \Magento\Swatches\Helper\Data
     * @since 2.0.0
     */
    protected $swatchHelper;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @since 2.0.0
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
     * @since 2.0.0
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
