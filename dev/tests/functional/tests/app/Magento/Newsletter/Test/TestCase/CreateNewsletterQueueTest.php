<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\TestCase;

use Magento\Newsletter\Test\Fixture\Queue;
use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueue;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateQueueIndex;

/**
 * Test to create Newsletter Queue.
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
 * 5. Fill data from fixtures
 * 6. Save Newsletter Queue
 *
 * @group Newsletters
 * @ZephyrId MAGETWO-71653
 */
class CreateNewsletterQueueTest extends Injectable
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
    private $templateIndex;

    /**
     * Page for edit newsletter queue.
     *
     * @var TemplateQueue
     */
    private $templateQueue;

    /**
     * Inject newsletter page.
     *
     * @param TemplateIndex $templateIndex
     * @param TemplateQueue $templateQueue
     * @return void
     */
    public function __inject(
        TemplateIndex $templateIndex,
        TemplateQueue $templateQueue
    ) {
        $this->templateIndex = $templateIndex;
        $this->templateQueue = $templateQueue;
    }

    /**
     * @param Template $newsletter
     * @param Queue $queue
     * @return void
     */
    public function test(Template $newsletter, Queue $queue)
    {
        // Preconditions
        $newsletter->persist();

        // Steps
        $this->templateIndex->open();
        $this->templateIndex->getNewsletterTemplateGrid()->search(['code' => $newsletter->getCode()]);
        $this->templateIndex->getNewsletterTemplateGrid()->performAction('Queue Newsletter');
        $this->templateQueue->getEditForm()->fill($queue);
        $this->templateQueue->getFormPageActions()->save();
    }
}
