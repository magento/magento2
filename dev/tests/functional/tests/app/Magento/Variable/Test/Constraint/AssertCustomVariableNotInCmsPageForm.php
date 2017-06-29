<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Variable\Test\Fixture\SystemVariable;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that custom system variable not in cms page form.
 */
class AssertCustomVariableNotInCmsPageForm extends AbstractConstraint
{
    /**
     * Assert that custom system variable not in cms page form.
     *
     * @param CmsPageNew $cmsPageNew
     * @param SystemVariable $systemVariable
     * @return void
     */
    public function processAssert(
        CmsPageNew $cmsPageNew,
        SystemVariable $systemVariable
    ) {
        $customVariableName = $systemVariable->getName();
        $cmsPageNew->open();
        $cmsPageForm = $cmsPageNew->getPageForm();
        $variables = $cmsPageForm->getSystemVariables();

        \PHPUnit_Framework_Assert::assertFalse(
            in_array($customVariableName, $variables),
            'Custom System Variable "' . $customVariableName . '" is present in Cms Page Form.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Custom System Variable is absent in Cms Page Form.";
    }
}
