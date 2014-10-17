<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Newsletter\Test\TestCase;

use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateEdit;
use Mtf\TestCase\Injectable;

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
