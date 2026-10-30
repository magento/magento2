<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Widget;

/**
 * Class for generation of JSON for building tree catalog.
 *
 * Examples of use:
 * \Magento\Catalog\Block\Adminhtml\Category\Tree::getLoadTreeUrl
 * \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser::getLoadTreeUrl
 */
class CategoriesJson extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget\CategoriesJson
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::quote';
}
