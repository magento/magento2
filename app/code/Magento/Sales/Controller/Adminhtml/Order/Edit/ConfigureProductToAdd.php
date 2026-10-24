<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Edit;

class ConfigureProductToAdd extends \Magento\Sales\Controller\Adminhtml\Order\Create\ConfigureProductToAdd
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';
}
