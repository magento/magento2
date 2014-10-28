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

use Magento\Store\Test\Fixture\Store;
use Mtf\Constraint\AbstractAssertForm;
use Magento\Core\Test\Fixture\SystemVariable;
use Magento\Core\Test\Page\Adminhtml\SystemVariableIndex;
use Magento\Core\Test\Page\Adminhtml\SystemVariableNew;

/**
 * Class AssertCustomVariableForm
 * Check that data at the form corresponds to the fixture data
 */
class AssertCustomVariableForm extends AbstractAssertForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Skipped fields for verify data
     *
     * @var array
     */
    protected $skippedFields = ['use_default_value', 'variable_id'];

    /**
     * Assert that data at the form corresponds to the fixture data
     *
     * @param SystemVariable $customVariable
     * @param SystemVariableIndex $systemVariableIndex
     * @param SystemVariableNew $systemVariableNew
     * @param Store $storeOrigin
     * @param SystemVariable $customVariableOrigin
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        SystemVariable $customVariable,
        SystemVariableIndex $systemVariableIndex,
        SystemVariableNew $systemVariableNew,
        Store $storeOrigin = null,
        SystemVariable $customVariableOrigin = null
    ) {
        // Prepare data
        $data = ($customVariableOrigin === null)
            ? $customVariable->getData()
            : array_merge($customVariableOrigin->getData(), $customVariable->getData());
        if ($customVariableOrigin !== null) {
            $dataOrigin = $data;
            $dataOrigin['html_value'] = $customVariableOrigin->getHtmlValue();
            $dataOrigin['plain_value'] = $customVariableOrigin->getPlainValue();
        } else {
            $dataOrigin = $data;
        }
        if ($data['html_value'] == '') {
            $data['html_value'] = $customVariableOrigin->getHtmlValue();
            $data['use_default_value'] = 'Yes';
        }
        $data['plain_value'] = ($data['plain_value'] == '')
            ? $customVariableOrigin->getPlainValue()
            : $data['plain_value'];
        // Perform assert
        $systemVariableIndex->open();
        $systemVariableIndex->getSystemVariableGrid()->searchAndOpen(['code' => $data['code']]);

        $formData = $systemVariableNew->getSystemVariableForm()->getData($customVariable);
        $errors = $this->verifyData($dataOrigin, $formData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);

        if ($storeOrigin !== null) {
            $systemVariableNew->getFormPageActions()->selectStoreView($storeOrigin->getName());
            $formData = $systemVariableNew->getSystemVariableForm()->getData($customVariable);
            $errors = $this->verifyData($data, $formData);
            \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
        }
    }

    /**
     * Text success verify Custom Variable
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed Custom Variable data on edit page(backend) equals to passed from fixture.';
    }
}
