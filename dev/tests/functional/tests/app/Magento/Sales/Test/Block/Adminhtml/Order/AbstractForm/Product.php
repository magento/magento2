<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\AbstractForm;

use Mtf\Block\Form;

/**
 * Class Product
 * Item product form on items block
 */
class Product extends Form
{
    /**
     * Fill item product data
     *
     * @param array $data
     * @return void
     */
    public function fillProduct(array $data)
    {
        $data = $this->dataMapping($data);
        $this->_fill($data);
    }
}
