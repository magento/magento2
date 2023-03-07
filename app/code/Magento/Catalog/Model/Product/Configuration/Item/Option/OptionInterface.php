<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface of product configurational item option
 *
 * @api
 */
namespace Magento\Catalog\Model\Product\Configuration\Item\Option;

/**
 * Interface \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
 *
 */
interface OptionInterface
{
    /**
     * Retrieve value associated with this option
     *
     * @return mixed
     */
    public function getValue();
}
