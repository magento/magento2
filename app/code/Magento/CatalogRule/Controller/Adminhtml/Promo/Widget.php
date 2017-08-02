<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo;

use Magento\Backend\App\Action;

/**
 * Class \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget
 *
 * @since 2.0.0
 */
abstract class Widget extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_CatalogRule::promo_catalog';
}
