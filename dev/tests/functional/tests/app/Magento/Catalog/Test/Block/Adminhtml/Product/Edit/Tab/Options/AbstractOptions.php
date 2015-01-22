<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Abstract class AbstractOptions
 * Parent class for all forms of product options
 */
abstract class AbstractOptions extends Tab
{
    /**
     * Fills in the form of an array of input data
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return $this
     */
    public function fillOptions(array $fields, SimpleElement $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Getting options data form on the product form
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return $this
     */
    public function getDataOptions(array $fields = null, SimpleElement $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $mapping = $this->dataMapping($fields);

        return $this->_getData($mapping, $element);
    }
}
