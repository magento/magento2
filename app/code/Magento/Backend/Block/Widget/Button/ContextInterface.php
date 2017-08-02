<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Button;

/**
 * Interface \Magento\Backend\Block\Widget\Button\ContextInterface
 *
 * @since 2.0.0
 */
interface ContextInterface
{
    /**
     * Check whether button rendering is allowed in current context
     *
     * @param \Magento\Backend\Block\Widget\Button\Item $item
     * @return bool
     * @api
     * @since 2.0.0
     */
    public function canRender(\Magento\Backend\Block\Widget\Button\Item $item);
}
