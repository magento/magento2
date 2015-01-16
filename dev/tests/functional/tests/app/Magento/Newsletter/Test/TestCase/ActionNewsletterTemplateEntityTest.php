<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\TestCase;

use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for Action for Newsletter Template (Preview and Queue)
 *
 * Test Flow:
 * Preconditions:
 * 1. Create newsletter
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Marketing > Newsletter Template
 * 3. Find created template in grid
 * 4. Select action in action dropdown for created template according to dataSet
 * 5. Perform all assertions
 *
 * @group Newsletters_(MX)
 * @ZephyrId MAGETWO-27043
 */
class ActionNewsletterTemplateEntityTest extends Injectable
{
    /**
     * Page with newsletter template grid
     *
     * @var TemplateIndex
     */
    protected $templateIndex;

    /**
     * Inject newsletter page
     *
     * @param TemplateIndex $templateIndex
     * @return void
     */
    public function __inject(TemplateIndex $templateIndex)
    {
        $this->templateIndex = $templateIndex;
    }

    /**
     * Action for Newsletter Template
     *
     * @param Template $newsletter
     * @param string $action
     * @return void
     */
    public function test(Template $newsletter, $action)
    {
        // Preconditions
        $newsletter->persist();

        // Steps
        $this->templateIndex->open();
        $this->templateIndex->getNewsletterTemplateGrid()->search(['code' => $newsletter->getCode()]);
        $this->templateIndex->getNewsletterTemplateGrid()->performAction($action);
    }
}
