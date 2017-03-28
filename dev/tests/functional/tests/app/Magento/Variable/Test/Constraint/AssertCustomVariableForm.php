<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Constraint;

use Magento\Variable\Test\Fixture\SystemVariable;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableIndex;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableNew;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Check that data at the form corresponds to the fixture data.
 */
class AssertCustomVariableForm extends AbstractAssertForm
{
    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $skippedFields = ['use_default_value', 'variable_id'];

    /**
     * Assert that data at the form corresponds to the fixture data.
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
            $dataOrigin = $this->arrayCopy($data);
            $dataOrigin['html_value'] = $customVariableOrigin->getHtmlValue();
            $dataOrigin['plain_value'] = $customVariableOrigin->getPlainValue();
        } else {
            $dataOrigin = $this->arrayCopy($data);
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

        $formData = $systemVariableNew->getSystemVariableForm()->getData();
        $errors = $this->verifyData($dataOrigin, $formData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);

        if ($storeOrigin !== null) {
            $systemVariableNew->getFormPageActions()->selectStoreView($storeOrigin->getName());
            $formData = $systemVariableNew->getSystemVariableForm()->getData();
            $errors = $this->verifyData($data, $formData);
            \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
        }
    }

    /**
     * Text success verify Custom Variable.
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed Custom Variable data on edit page(backend) equals to passed from fixture.';
    }

    /**
     * Return a copy of the source array.
     * To workaround an issue that an array assignment is handled by reference in PHP 7.0.5 in certain situation.
     *
     * @param array $sourceArray
     * @return array
     */
    private function arrayCopy($sourceArray)
    {
        $copyArray = [];
        foreach ($sourceArray as $key => $value) {
            $copyArray[$key] = $value;
        }
        return $copyArray;
    }
}
