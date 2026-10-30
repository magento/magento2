<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Backend form key content block
 */
namespace Magento\Backend\Block\Admin;

/**
 * @api
 * @since 100.0.2
 */
class Formkey extends \Magento\Backend\Block\Template
{
    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
