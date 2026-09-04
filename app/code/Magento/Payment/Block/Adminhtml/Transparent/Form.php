<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Block\Adminhtml\Transparent;

/**
 * @api
 * @since 100.0.2
 */
class Form extends \Magento\Payment\Block\Transparent\Form
{
    /**
     * On backend this block does not have any conditional checks
     *
     * @return bool
     */
    protected function shouldRender()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function initializeMethod()
    {
        // @codingStandardsIgnoreStart
        return;
        // @codingStandardsIgnoreEnd
    }
}
