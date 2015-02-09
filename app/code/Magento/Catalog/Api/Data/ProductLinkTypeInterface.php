<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface ProductLinkTypeInterface
{
    /**
     * Get link type code
     *
     * @return int
     */
    public function getCode();

    /**
     * Set link type code
     *
     * @param int $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get link type name
     *
     * @return string
     */
    public function getName();

    /**
     * Set link type name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);
}
