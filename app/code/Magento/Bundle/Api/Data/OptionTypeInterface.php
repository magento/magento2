<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api\Data;

interface OptionTypeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
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

    /**
     * Get type code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set type code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);
}
