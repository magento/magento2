<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueueIndex;

/**
 * Assert that Newsletter Queue success save message is present.
 */
class AssertNewsletterQueueSaveMessage extends AbstractAssertForm
{
    /**
     * Text value to be checked.
     */
    const SUCCESS_MESSAGE = 'You saved the newsletter queue.';
    
    /**
     * Assert that Newsletter Queue success save message is present.
     *
     * @param TemplateQueueIndex $indexQueue
     * @return void
     */
    public function processAssert(
        TemplateQueueIndex $indexQueue
    ) {
        $actualMessages = $indexQueue->getMessagesBlock()->getSuccessMessages();
        \PHPUnit\Framework\Assert::assertContains(
            self::SUCCESS_MESSAGE,
            $actualMessages,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual:\n" . implode("\n - ", $actualMessages)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Newsletter Queue success save message is present.';
    }
}
