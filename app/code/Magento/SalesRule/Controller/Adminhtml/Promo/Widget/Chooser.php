<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Widget;

class Chooser extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget\Chooser
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::quote';
}
