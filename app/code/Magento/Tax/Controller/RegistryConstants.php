<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller;

/**
 * Declarations of core registry keys used by the Tax module
 *
 */
class RegistryConstants
{
    /**
     * Registry key where current tax ID is stored
     */
    const CURRENT_TAX_RATE_ID = 'current_tax_rate_id';

    /**
     * Registry key where current tax rate form data is stored
     */
    const CURRENT_TAX_RATE_FORM_DATA = 'current_tax_rate_form_data';
}
