<?php
/**
 * @category    Mtf
 * @package     Mtf
 * @subpackage  functional_tests
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Type;

use Mtf\Block\Form;

/**
 * Class Option
 * Bundle option
 */
class Option extends Form
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
        $this->_fill($mapping);
    }
}
