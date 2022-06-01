<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer;

/**
 * Interface \Magento\Catalog\Model\Layer\StateKeyInterface
 *
 * @api
 */
interface StateKeyInterface
{
    /**
     * Build state key
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function toString($category);
}
