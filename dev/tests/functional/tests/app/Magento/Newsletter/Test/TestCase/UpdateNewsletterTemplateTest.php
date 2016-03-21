<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\TestCase;

use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateEdit;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateNewsletterTemplate
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create newsletter template
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to MARKETING > Newsletter Template
 * 3. Open Template from preconditions
 * 4. Fill in all data according to data set
 * 5. Click 'Save Template' button
 * 6. Perform asserts
 *
 * @group Newsletters_(MX)
 * @ZephyrId MAGETWO-29427
 */
class UpdateNewsletterTemplateTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Newsletter template index page
     *
     * @var TemplateIndex
     */
    protected $templateIndex;

    /**
     * Newsletter template edit page
     *
     * @var TemplateEdit
     */
    protected $templateEdit;

    /**
     * Injection data
     *
     * @param TemplateIndex $templateIndex
     * @param TemplateEdit $templateEdit
     * @return void
     */
    public function __inject(TemplateIndex $templateIndex, TemplateEdit $templateEdit)
    {
        $this->templateIndex = $templateIndex;
        $this->templateEdit = $templateEdit;
    }

    /**
     * Run Update Newsletter test
     *
     * @param Template $templateInitial
     * @param Template $template
     * @return void
     */
    public function test(Template $templateInitial, Template $template)
    {
        // Preconditions:
        $templateInitial->persist();

        // Steps:
        $this->templateIndex->open();
        $this->templateIndex->getNewsletterTemplateGrid()->searchAndOpen(['code' => $templateInitial->getCode()]);
        $this->templateEdit->getEditForm()->fill($template);
        $this->templateEdit->getFormPageActions()->save();
    }
}
