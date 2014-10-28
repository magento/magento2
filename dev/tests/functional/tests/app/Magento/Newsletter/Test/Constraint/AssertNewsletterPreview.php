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

namespace Magento\Newsletter\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplatePreview;

/**
 * Class AssertNewsletterPreview
 * Assert that newsletter preview opened in new window and template content correct
 */
class AssertNewsletterPreview extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that newsletter preview opened in new window and template content correct
     *
     * @param Browser $browser
     * @param TemplatePreview $templatePreview
     * @param Template $newsletter
     * @return void
     */
    public function processAssert(Browser $browser, TemplatePreview $templatePreview, Template $newsletter)
    {
        $browser->selectWindow();
        $content = $templatePreview->getContent()->getPageContent();
        $browser->closeWindow();
        \PHPUnit_Framework_Assert::assertEquals(
            $content,
            $newsletter->getText(),
            'Template content not correct information.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Newsletter preview opened in new window and has valid content.';
    }
}
