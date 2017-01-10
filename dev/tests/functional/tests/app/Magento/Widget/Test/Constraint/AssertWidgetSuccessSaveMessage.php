<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that success message is displayed after widget saved
 */
class AssertWidgetSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Text value to be checked
     */
    const SUCCESS_MESSAGE = 'The widget instance has been saved.';

    /**
     * Assert that success message is displayed after widget saved
     *
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @return void
     */
    public function processAssert(WidgetInstanceIndex $widgetInstanceIndex)
    {
        $actualMessage = $widgetInstanceIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
        );
    }

    /**
     * Text of Created Widget Success Message assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Widget success create message is present.';
    }
}
