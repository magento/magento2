<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Block\Widget\Button;

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
