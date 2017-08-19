<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Edit;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Edit\ConfigureProductToAdd
 *
 */
class ConfigureProductToAdd extends \Magento\Sales\Controller\Adminhtml\Order\Create\ConfigureProductToAdd
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';
}
