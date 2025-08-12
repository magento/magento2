<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;

/**
 * System admin controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class System extends AbstractAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Backend::system';
}
