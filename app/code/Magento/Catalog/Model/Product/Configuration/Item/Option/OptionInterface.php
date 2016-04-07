<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface of product configurational item option
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Configuration\Item\Option;

interface OptionInterface
{
    /**
     * Retrieve value associated with this option
     *
     * @return mixed
     */
    public function getValue();
}
