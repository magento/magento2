<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get type label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);
}
