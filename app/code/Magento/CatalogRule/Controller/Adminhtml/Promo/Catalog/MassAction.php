<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Backend\App\AbstractAction;

abstract class MassAction extends AbstractAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_CatalogRule::promo_catalog';
}
