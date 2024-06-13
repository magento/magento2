<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Adminhtml Currency Symbols Controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System;

/**
 * @api
 * @since 100.0.2
 */
abstract class Currencysymbol extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_CurrencySymbol::symbols';
}
