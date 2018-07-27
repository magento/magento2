<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Adminhtml\Transparent;

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
     * {inheritdoc}
     */
    protected function initializeMethod()
    {
        return;
    }
}
