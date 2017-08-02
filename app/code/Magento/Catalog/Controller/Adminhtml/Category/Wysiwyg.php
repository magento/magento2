<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Category\Wysiwyg
 *
 * @since 2.0.0
 */
class Wysiwyg extends \Magento\Catalog\Controller\Adminhtml\Product\Wysiwyg
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::categories';
}
