<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Button;

/**
 * Interface \Magento\Backend\Block\Widget\Button\ContextInterface
 *
 */
interface ContextInterface
{
    /**
     * Check whether button rendering is allowed in current context
     *
     * @param \Magento\Backend\Block\Widget\Button\Item $item
     * @return bool
     * @api
     */
    public function canRender(\Magento\Backend\Block\Widget\Button\Item $item);
}
