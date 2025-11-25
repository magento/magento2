<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogWidget\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;

/**
 * Class Widget
 */
abstract class Widget extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';
}
