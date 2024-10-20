<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\View;

/**
 * Product additional info block
 *
 * @api
 * @since 100.0.2
 */
class Additional extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $_list;

    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::product/view/additional.phtml';

    /**
     * Return the HTML for the child list
     *
     * @return array
     */
    public function getChildHtmlList()
    {
        if ($this->_list === null) {
            $this->_list = [];
            $layout = $this->getLayout();
            foreach ($this->getChildNames() as $name) {
                $this->_list[] = $layout->renderElement($name);
            }
        }
        return $this->_list;
    }
}
