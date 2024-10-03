<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Button;

/**
 * Interface \Magento\Backend\Block\Widget\Button\ToolbarInterface
 *
 * @api
 */
interface ToolbarInterface
{
    /**
     * Push buttons into toolbar
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @return void
     */
    public function pushButtons(
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    );
}
