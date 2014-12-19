<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertNewsletterSuccessCreateMessage
 *
 * @package Magento\Newsletter\Test\Constraint
 */
class AssertNewsletterSuccessCreateMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_MESSAGE = 'The newsletter template has been saved.';

    /**
     * Assert that success message is displayed after newsletter template save
     *
     * @param TemplateIndex $templateIndex
     */
    public function processAssert(TemplateIndex $templateIndex)
    {
        $actualMessage = $templateIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Success assert of created newsletter template success message
     *
     * @return string
     */
    public function toString()
    {
        return 'Newsletter success save message is present.';
    }
}
