<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface CategoryProductLinkInterface
{
    /**
     * @return string|null
     */
    public function getSku();

    /**
     * @return int|null
     */
    public function getPosition();

    /**
     * Get category id
     *
     * @return string
     */
    public function getCategoryId();
}
