<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend form key content block
 */
namespace Magento\Backend\Block\Admin;

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
