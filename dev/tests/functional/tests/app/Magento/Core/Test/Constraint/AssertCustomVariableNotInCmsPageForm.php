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

namespace Magento\Core\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsNew;
use Magento\Core\Test\Fixture\SystemVariable;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomVariableNotInCmsPageForm
 */
class AssertCustomVariableNotInCmsPageForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
