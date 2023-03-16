<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Widget;

use Magento\CatalogRule\Controller\Adminhtml\Promo\Widget\Chooser as PromoWidgetChooser;

class Chooser extends PromoWidgetChooser
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::quote';
}
