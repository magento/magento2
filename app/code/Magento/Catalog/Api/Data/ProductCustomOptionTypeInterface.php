<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface ProductCustomOptionTypeInterface
{
    /**
     * Get option type label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set option type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get option type code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set option type code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get option type group
     *
     * @return string
     */
    public function getGroup();

    /**
     * Set option type group
     *
     * @param string $group
     * @return $this
     */
    public function setGroup($group);
}
