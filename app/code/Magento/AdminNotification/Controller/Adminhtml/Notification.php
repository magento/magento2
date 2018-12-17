<?php
/**
 * Adminhtml AdminNotification controller
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;

/**
 * Class Notification
 *
 * @package Magento\AdminNotification\Controller\Adminhtml
 * @api
 * @since 100.0.2
 */
abstract class Notification extends AbstractAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::show_list';
}
