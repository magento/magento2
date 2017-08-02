<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Adminhtml\Transparent;

/**
 * @api
 * @since 2.0.0
 */
class Form extends \Magento\Payment\Block\Transparent\Form
{
    /**
     * On backend this block does not have any conditional checks
     *
     * @return bool
     * @since 2.0.0
     */
    protected function shouldRender()
    {
        return true;
    }

    /**
     * {inheritdoc}
     * @since 2.0.0
     */
    protected function initializeMethod()
    {
        return;
    }
}
