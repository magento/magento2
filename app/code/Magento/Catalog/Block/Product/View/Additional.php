<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product additional info block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\View;

class Additional extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $_list;

    /**
     * @var string
     */
    protected $_template = 'product/view/additional.phtml';

    /**
     * @return array
     */
    public function getChildHtmlList()
    {
        if (is_null($this->_list)) {
            $this->_list = [];
            $layout = $this->getLayout();
            foreach ($this->getChildNames() as $name) {
                $this->_list[] = $layout->renderElement($name);
            }
        }
        return $this->_list;
    }
}
