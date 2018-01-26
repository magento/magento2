<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\TestCase;

use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateNewIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for Create Newsletter Template
 *
 * Test Flow:
 * 1. Login to backend.
 * 2. Navigate to MARKETING > Newsletter Template.
 * 3. Add New Template.
 * 4. Fill in all data according to data set.
 * 5. Save.
 * 6. Perform asserts.
 *
 * @group Newsletters_(MX)
 * @ZephyrId MAGETWO-23302
 */
class CreateNewsletterTemplateEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Page for create newsletter template
     *
     * @var TemplateNewIndex
     */
    protected $templateNewIndex;

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
     * @param TemplateNewIndex $templateNewIndex
     */
    public function __inject(
        TemplateIndex $templateIndex,
        TemplateNewIndex $templateNewIndex
    ) {
        $this->templateIndex = $templateIndex;
        $this->templateNewIndex = $templateNewIndex;
    }

    /**
     * Create newsletter template
     *
     * @param Template $template
     */
    public function testCreateNewsletterTemplate(Template $template)
    {
        // Steps
        $this->templateIndex->open();
        $this->templateIndex->getGridPageActions()->addNew();
        $this->templateNewIndex->getEditForm()->fill($template);
        $this->templateNewIndex->getFormPageActions()->save();
    }
}
