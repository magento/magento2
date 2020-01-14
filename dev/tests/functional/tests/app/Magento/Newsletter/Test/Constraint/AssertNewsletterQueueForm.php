<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Newsletter\Test\Fixture\Queue;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueue;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueueIndex;

/**
 * Assert that Newsletter Queue form data equal the fixture data.
 */
class AssertNewsletterQueueForm extends AbstractAssertForm
{
    /**
     * Assert that Newsletter Queue form data equal the fixture data.
     *
     * @param TemplateQueueIndex $indexQueue
     * @param TemplateQueue $templateQueue
     * @param Queue $queue
     * @return void
     */
    public function processAssert(
        TemplateQueueIndex $indexQueue,
        TemplateQueue $templateQueue,
        Queue $queue
    ) {
        $indexQueue->open();
        $indexQueue->getQueueTemplateGrid()->searchAndOpen(['newsletter_subject' => $queue->getNewsletterSubject()]);

        $dataDiff = $this->verifyData($queue->getData(), $templateQueue->getEditForm()->getData($queue));
        \PHPUnit\Framework\Assert::assertEmpty($dataDiff, $dataDiff);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Newsletter Queue form data equal the fixture data.';
    }
}
