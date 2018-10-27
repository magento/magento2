<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

declare(strict_types=1);

=======
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
     * Authorization level of a basic admin session.
=======
     * Authorization level of a basic admin session
>>>>>>> upstream/2.2-develop
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::quote';
}
