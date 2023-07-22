<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Locked administrators page
 *
 * @api
 * @since 100.0.2
 */
class Locks extends Container
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
