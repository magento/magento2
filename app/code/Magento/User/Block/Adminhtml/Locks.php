<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Block\Adminhtml;

/**
 * Locked administrators page
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
