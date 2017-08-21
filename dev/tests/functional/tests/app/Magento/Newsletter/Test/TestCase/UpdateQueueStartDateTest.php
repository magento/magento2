<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\TestCase;

use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueue;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueueIndex;

/**
 * Test to update Start Date in Newsletter Queue.
 *
 * Test Flow:
 * Preconditions:
 * 1. Create newsletter
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Marketing > Newsletter Template
 * 3. Find created template in grid
 * 4. Execute "Queue Newsletter" action
 * 5. Fill Date Start
 * 6. Save Newsletter Queue
 *
 * @group Newsletters
 * @ZephyrId MAGETWO-71653
 */
class UpdateQueueStartDateTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const STABLE = 'no';
    /* end tags */

    /**
     * Page with newsletter template grid.
     *
     * @var TemplateIndex
     */
    protected $templateIndex;

    /**
     * Page with newsletter queue grid.
     *
     * @var TemplateQueueIndex
     */
    protected $indexQueue;

    /**
     * Page for edit newsletter queue.
     *
     * @var TemplateQueue
     */
    protected $templateQueue;

    /**
     * Inject newsletter page.
     *
     * @param TemplateIndex $templateIndex
     * @param TemplateQueueIndex $indexQueue
     * @param TemplateQueue $templateQueue
     * @return void
     */
    public function __inject(
        TemplateIndex $templateIndex,
        TemplateQueueIndex $indexQueue,
        TemplateQueue $templateQueue
    ) {
        $this->templateIndex = $templateIndex;
        $this->indexQueue = $indexQueue;
        $this->templateQueue = $templateQueue;
    }

    /**
     * @param Template $newsletter
     * @param string $date
     * @return void
     */
    public function test(Template $newsletter)
    {
        // Preconditions
        $newsletter->persist();

        // Steps
        $this->templateIndex->open();
        $this->templateIndex->getNewsletterTemplateGrid()->search(['code' => $newsletter->getCode()]);
        $this->templateIndex->getNewsletterTemplateGrid()->performAction('Queue Newsletter');
        $this->templateQueue->getFormPageActions()->save();
    }
}
