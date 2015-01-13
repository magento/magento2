<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Coupon;

interface CodegeneratorInterface
{
    /**
     * Retrieve generated code
     *
     * @return string
     */
    public function generateCode();

    /**
     * Retrieve delimiter
     *
     * @return string
     */
    public function getDelimiter();
}
