<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\Block\Widget\Button;

/**
 * Interface \Magento\Backend\Block\Widget\Button\ContextInterface
 *
 * @api
 */
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
