<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Newsletter\Test\Fixture\Queue;
use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueue;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueueIndex;

/**
 * Assert that "Newsletter Queue" saved correctly.
 */
class AssertNewsletterQueueSave extends AbstractAssertForm
{
    /**
     * Assert that "Newsletter Queue" saved correctly.
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
        $indexQueue->getMessagesBlock()->assertSuccessMessage();
        $indexQueue->getQueueTemplateGrid()->searchAndOpen(['newsletter_subject' => $queue->getNewsletterSubject()]);

        $dataDiff = $this->verifyData($queue->getData(), $templateQueue->getEditForm()->getData($queue));
        \PHPUnit_Framework_Assert::assertEmpty($dataDiff, $dataDiff);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return '"Newsletter Queue" saved correctly';
    }
}
