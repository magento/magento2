<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Button;

interface ContextInterface
{
    /**
     * Check whether button rendering is allowed in current context
     *
     * @param \Magento\Backend\Block\Widget\Button\Item $item
     * @return bool
     */
    public function canRender(\Magento\Backend\Block\Widget\Button\Item $item);
}
