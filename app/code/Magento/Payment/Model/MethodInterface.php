<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
