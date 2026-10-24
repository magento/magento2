<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\User\Block\Adminhtml;

/**
 * Locked administrators page
 *
 * @api
 * @since 100.0.2
 */
class Locks extends \Magento\Backend\Block\Widget\Grid\Container
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
