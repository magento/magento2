<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column;

/**
 * Grid column block that is displayed only in multistore mode
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Multistore extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Get header css class name
     *
     * @return string
     */
    public function isDisplayed()
    {
        return !$this->_storeManager->isSingleStoreMode();
    }
}
