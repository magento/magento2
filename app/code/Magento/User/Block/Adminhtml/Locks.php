<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Block\Adminhtml;

/**
 * Locked administrators page
 *
 * @api
 * @since 2.0.0
 */
class Locks extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
