<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Newsletter\Test\Fixture\Queue;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueueIndex;

/**
 * Assert that Newsletter Queue is present in grid.
 */
class AssertNewsletterQueueInGrid extends AbstractAssertForm
{
    /**
     * Assert that Newsletter Queue is present in grid.
     *
     * @param TemplateQueueIndex $indexQueue
     * @param Queue $queue
     * @return void
     */
    public function processAssert(
        TemplateQueueIndex $indexQueue,
        Queue $queue
    ) {
        $startAt = strftime("%b %e, %Y", strtotime($queue->getQueueStartAt()));
        $filter = [
            'newsletter_subject' => $queue->getNewsletterSubject(),
            'start_at_from' => $startAt,
            'start_at_to' => $startAt,
        ];

        $indexQueue->open();
        $indexQueue->getQueueTemplateGrid()->search(['newsletter_subject' => $queue->getNewsletterSubject()]);

        \PHPUnit_Framework_Assert::assertTrue(
            $indexQueue->getQueueTemplateGrid()->isRowVisible($filter, false, false),
            'Newsletter Queue \'' . $queue->getNewsletterSubject() . '\' is absent in grid.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Newsletter Queue is present in grid.';
    }
}
