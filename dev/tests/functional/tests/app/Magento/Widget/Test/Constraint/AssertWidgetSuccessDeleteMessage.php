<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that Widget success delete message presents
 */
class AssertWidgetSuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Message displayed after delete widget
     */
    const DELETE_MESSAGE = 'The widget instance has been deleted.';

    /**
     * Assert that Widget success delete message is present
     *
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @return void
     */
    public function processAssert(WidgetInstanceIndex $widgetInstanceIndex)
    {
        $actualMessage = $widgetInstanceIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::DELETE_MESSAGE,
            $actualMessage,
            'Wrong widget success delete message is displayed.'
        );
    }

    /**
     * Text of Delete Widget Success Message assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Widget success delete message is present.';
    }
}
