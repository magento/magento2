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

use Mtf\TestCase\Injectable;
use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;

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
