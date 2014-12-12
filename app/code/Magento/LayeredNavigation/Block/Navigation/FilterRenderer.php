<?php
/**
 * Catalog layer filter renderer
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\LayeredNavigation\Block\Navigation;

use Magento\Framework\View\Element\Template;

class FilterRenderer extends \Magento\Framework\View\Element\Template implements
    \Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface
{
    /**
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @return string
     */
    public function render(\Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter)
    {
        $this->assign('filterItems', $filter->getItems());
        $html = $this->_toHtml();
        $this->assign('filterItems', []);
        return $html;
    }
}
