<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
