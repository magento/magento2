<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
