<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface of product configurational item option
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Configuration\Item\Option;

/**
 * Interface \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
 *
 * @since 2.0.0
 */
interface OptionInterface
{
    /**
     * Retrieve value associated with this option
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getValue();
}
