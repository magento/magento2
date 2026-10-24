<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;

/**
 * Interface FilterRendererInterface
 * @api
 * @since 100.0.2
 */
interface FilterRendererInterface
{
    /**
     * Render filter
     *
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter);
}
