<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form Input/Output Filter Interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Filter;

/**
 * @api
 * @since 2.0.0
 */
interface FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    public function inputFilter($value);

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    public function outputFilter($value);
}
