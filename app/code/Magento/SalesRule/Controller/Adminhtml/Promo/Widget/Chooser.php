<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Widget;

/**
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Widget\Chooser
 *
 */
class Chooser extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget\Chooser
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::quote';
}
