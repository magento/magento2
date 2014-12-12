<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Api\Data;

interface ProductAttributeTypeInterface
{
    const VALUE = 'value';

    const LABEL = 'label';

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();

    /**
     * Get type label
     *
     * @return string
     */
    public function getLabel();
}
