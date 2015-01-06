<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
