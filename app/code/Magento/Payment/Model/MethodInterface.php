<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Payment interface
 */
namespace Magento\Payment\Model;

interface MethodInterface
{
    /**
     * Retrieve payment method code
     *
     * @return string
     */
    public function getCode();

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType();

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle();
}
