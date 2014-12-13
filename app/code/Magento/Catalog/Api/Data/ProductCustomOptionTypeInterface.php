<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * Get option type code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get option type group
     *
     * @return string
     */
    public function getGroup();
}
