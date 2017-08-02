<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml Currency Symbols Controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System;

/**
 * @api
 * @since 2.0.0
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
