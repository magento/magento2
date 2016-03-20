<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateEdit;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertNewsletterForm
 * Assert that newsletter template form data equals to data passed from fixture
 */
class AssertNewsletterForm extends AbstractAssertForm
{
    /**
     * Assert that newsletter template form data equals to data passed from fixture
     *
     * @param TemplateIndex $templateIndex
     * @param TemplateEdit $templateEdit
     * @param Template $template
     * @return void
     */
    public function processAssert(TemplateIndex $templateIndex, TemplateEdit $templateEdit, Template $template)
    {
        $templateIndex->open()->getNewsletterTemplateGrid()->searchAndOpen(['code' => $template->getCode()]);
        $errors = $this->verifyData($template->getData(), $templateEdit->getEditForm()->getData($template));

        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function toString()
    {
        return "Newsletter template form data equals to data passed from fixture.";
    }
}
