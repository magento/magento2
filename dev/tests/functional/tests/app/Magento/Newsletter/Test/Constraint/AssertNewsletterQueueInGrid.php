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
        $indexQueue->open();
        $filter = [
            'newsletter_subject' => $queue->getNewsletterSubject(),
        ];

        \PHPUnit_Framework_Assert::assertTrue(
            $indexQueue->getQueueTemplateGrid()->isRowVisible($filter),
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
