<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Edit;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Edit\LoadBlock
 *
 * @since 2.0.0
 */
class LoadBlock extends \Magento\Sales\Controller\Adminhtml\Order\Create\LoadBlock
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';
}
