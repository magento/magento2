<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Interface of product configurational item option
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
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
