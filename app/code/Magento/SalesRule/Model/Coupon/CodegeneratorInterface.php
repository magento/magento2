<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Coupon;

/**
 * @api
 * @since 2.0.0
 */
interface CodegeneratorInterface
{
    /**
     * Retrieve generated code
     *
     * @return string
     * @since 2.0.0
     */
    public function generateCode();

    /**
     * Retrieve delimiter
     *
     * @return string
     * @since 2.0.0
     */
    public function getDelimiter();
}
