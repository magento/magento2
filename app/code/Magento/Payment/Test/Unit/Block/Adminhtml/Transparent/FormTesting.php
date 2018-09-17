<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Block\Adminhtml\Transparent;

use Magento\Payment\Block\Adminhtml\Transparent\Form;

/**
 * Class FormTesting extended test class, used to substitute calls to parent methods
 * @package Magento\Payment\Test\Unit\Block\Adminhtml\Transparent
 */
class FormTesting extends Form
{
    /**
     * Return values for processHtml() method
     */
    const PROCESS_HTML_RESULT = 'parent_result';

    /**
     * {inheritdoc}
     */
    protected function processHtml()
    {
        return self::PROCESS_HTML_RESULT;
    }
}
