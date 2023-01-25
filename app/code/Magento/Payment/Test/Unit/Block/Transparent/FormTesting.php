<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Block\Transparent;

use Magento\Payment\Block\Transparent\Form;

/**
 * Class FormTesting extended test class, used to substitute calls to parent methods
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
