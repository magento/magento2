<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface ProductLinkAttributeInterface
{
    /**
     * Get attribute code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set attribute code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get attribute type
     *
     * @return string
     */
    public function getType();

    /**
     * Set attribute type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);
}
