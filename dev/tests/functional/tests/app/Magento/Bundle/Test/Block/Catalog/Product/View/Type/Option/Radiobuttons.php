<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option;

use Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option;

/**
 * Class Radiobuttons
 * Bundle option radio button type
 */
class Radiobuttons extends Option
{
    /**
     * Set data in bundle option
     *
     * @param array $data
     * @return void
     */
    public function fillOption(array $data)
    {
        $mapping = $this->dataMapping($data);
        $mapping['name']['selector'] = str_replace('%product_name%', $data['name'], $mapping['name']['selector']);
        $mapping['name']['value'] = 'Yes';
        $this->_fill($mapping);
    }
}
