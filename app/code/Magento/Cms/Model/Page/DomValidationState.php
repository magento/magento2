<?php
/**
 * Application config file resolver
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Page;

/**
 * Class DomValidationState
 * @package Magento\Cms\Model\Page
 */
class DomValidationState implements \Magento\Framework\Config\ValidationStateInterface
{
    /**
     * Retrieve validation state
     * Used in cms page post processor to force validate layout update xml
     *
     * @return boolean
     */
    public function isValidationRequired()
    {
        return true;
    }
}
