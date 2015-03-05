<?php
/**
 * Product type details
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

interface ProductTypeInterface
{
    /**
     * Get product type code
     *
     * @return string
     */
    public function getName();

    /**
     * Set product type code
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get product type label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set product type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);
}
