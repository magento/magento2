<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Block\Navigation;

interface FilterRendererInterface
{
    /**
     * Render filter
     *
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @return string
     */
    public function render(\Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter);
}
