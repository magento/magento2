<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsNew;
use Magento\Core\Test\Fixture\SystemVariable;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomVariableNotInCmsPageForm
 */
class AssertCustomVariableNotInCmsPageForm extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that custom system variable not in cms page form
     *
     * @param CmsNew $cmsNewPage
     * @param SystemVariable $systemVariable
     * @return void
     */
    public function processAssert(
        CmsNew $cmsNewPage,
        SystemVariable $systemVariable
    ) {
        $customVariableName = $systemVariable->getName();
        $cmsNewPage->open();
        $cmsPageForm = $cmsNewPage->getPageForm();
        $variables = $cmsPageForm->getSystemVariables();

        \PHPUnit_Framework_Assert::assertFalse(
            in_array($customVariableName, $variables),
            'Custom System Variable "' . $customVariableName . '" is present in Cms Page Form.'
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return "Custom System Variable is absent in Cms Page Form.";
    }
}
