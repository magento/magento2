<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueue;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueueIndex;

/**
 * Assert that field "Queue Date Start" saved correctly.
 */
class AssertNewsletterQueueDateStart extends AbstractAssertForm
{
    /**
     * Assert that Field "Queue Date Start" saved correctly.
     *
     * @param TemplateQueueIndex $indexQueue
     * @param TemplateQueue $templateQueue
     * @param Template $newsletter
     * @param string $date
     * @return void
     */
    public function processAssert(
        TemplateQueueIndex $indexQueue,
        TemplateQueue $templateQueue,
        Template $newsletter,
        string $date
    ) {
        $indexQueue->open();
        $indexQueue->getQueueTemplateGrid()->searchAndOpen(['newsletter_subject' => $newsletter->getSubject()]);
        \PHPUnit_Framework_Assert::assertEquals(
            $date,
            $templateQueue->getEditForm()->getDateStart(),
            'Field "Queue Date Start" did\'t save correctly'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Field "Queue Date Start" saved correctly';
    }
}
