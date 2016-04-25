<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\TestCase;

use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateNewIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Newsletter Templates Preview on Newsletter Template page
 *
 * Test Flow:
 * Preconditions:
 * 1. Create newsletter
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to "Marketing" -> "Newsletter Template"
 * 3. Find created template in grid and open it
 * 4. Click "Preview Template" button at the top of the page
 * 5. Perform all assertions
 *
 * @group Newsletters_(MX)
 * @ZephyrId MAGETWO-51979
 */
class PreviewNewsletterTemplateEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Page with newsletter template grid
     *
     * @var TemplateIndex
     */
    protected $templateIndex;

    /**
     * Page for create newsletter template
     *
     * @var TemplateNewIndex
     */
    protected $templateNewIndex;

    /**
     * Inject newsletter page
     *
     * @param TemplateIndex $templateIndex
     * @param TemplateNewIndex $templateNewIndex
     * @return void
     */
    public function __inject(
        TemplateIndex $templateIndex,
        TemplateNewIndex $templateNewIndex
    ) {
        $this->templateIndex = $templateIndex;
        $this->templateNewIndex = $templateNewIndex;
    }

    /**
     * Action for Newsletter Template
     *
     * @param Template $newsletter
     * @return void
     */
    public function test(Template $newsletter)
    {
        // Preconditions
        $newsletter->persist();

        // Steps
        // 1. Open Backend
        // 2. Go to "Marketing" -> "Newsletter Template"
        $this->templateIndex->open();
        // 3. Find created template in grid and open it
        $this->templateIndex->getNewsletterTemplateGrid()->searchAndOpen(['code' => $newsletter->getCode()]);
        // 4. Click "Preview Template" button at the top of the page
        $this->templateNewIndex->getFormPageActions()->clickPreview();
    }
}
