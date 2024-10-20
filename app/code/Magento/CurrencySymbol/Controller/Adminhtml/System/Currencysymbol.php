<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Controller\Adminhtml\System;

/**
 * Adminhtml Currency Symbols Controller
 *
 * @phpcs:ignore Magento2.Classes.AbstractApi.AbstractApi
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
    public const ADMIN_RESOURCE = 'Magento_CurrencySymbol::symbols';
}
