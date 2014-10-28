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
use Magento\Newsletter\Test\Page\Adminhtml\TemplateNewIndex;
use Mtf\TestCase\Injectable;

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
