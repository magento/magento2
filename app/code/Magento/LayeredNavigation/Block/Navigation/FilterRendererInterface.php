<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;

/**
 * Interface FilterRendererInterface
 * @api
 * @since 2.0.0
 */
interface FilterRendererInterface
{
    /**
     * Render filter
     *
     * @param FilterInterface $filter
     * @return string
     * @since 2.0.0
     */
    public function render(FilterInterface $filter);
}
