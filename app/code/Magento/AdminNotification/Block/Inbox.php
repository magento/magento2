<?php
/**
 * Adminhtml AdminNotification inbox grid
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Block;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Inbox
 *
 * @package Magento\AdminNotification\Block
 * @api
 * @since 100.0.2
 */
class Inbox extends Container
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void //phpcs:ignore
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magento_AdminNotification';
        $this->_headerText = __('Messages Inbox');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
