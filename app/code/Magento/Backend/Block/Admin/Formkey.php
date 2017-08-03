<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend form key content block
 */
namespace Magento\Backend\Block\Admin;

/**
 * @api
 * @since 2.0.0
 */
class Formkey extends \Magento\Backend\Block\Template
{
    /**
     * Get form key
     *
     * @return string
     * @since 2.0.0
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
