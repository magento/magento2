<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that integration form filled correctly.
 */
class AssertIntegrationForm extends AbstractAssertForm
{
    /**
     * Skipped fields while verifying.
     *
     * @var array
     */
    protected $skippedFields = [
        'integration_id',
    ];

    /**
     * Pattern for error message.
     *
     * @var string
     */
    protected $errorMessagePattern = "Data in '%s' field not equal.\nExpected: %s\nActual: %s";

    /**
     * Flag for strict verify resources data.
     *
     * @var bool
     */
    protected $strictResourcesVerify;

    /**
     * Assert that integration form filled correctly.
     *
     * @param IntegrationIndex $integrationIndexPage
     * @param IntegrationNew $integrationNewPage
     * @param Integration $integration
     * @param Integration|null $initialIntegration
     * @param bool $strictResourcesVerify [optional]
     * @return void
     */
    public function processAssert(
        IntegrationIndex $integrationIndexPage,
        IntegrationNew $integrationNewPage,
        Integration $integration,
        Integration $initialIntegration = null,
        $strictResourcesVerify = false
    ) {
        $this->strictResourcesVerify = $strictResourcesVerify;
        $data = ($initialIntegration === null)
            ? $integration->getData()
            : array_merge($initialIntegration->getData(), $integration->getData());
        $filter = [
            'name' => $data['name'],
        ];

        $integrationIndexPage->open();
        $integrationIndexPage->getIntegrationGrid()->searchAndOpen($filter);
        $formData = $integrationNewPage->getIntegrationForm()->getData();
        unset($formData['current_password']);
        unset($data['current_password']);
        $dataDiff = $this->verifyForm($formData, $data);
        \PHPUnit\Framework\Assert::assertEmpty(
            $dataDiff,
            'Integration form was filled incorrectly.'
            . "\nLog:\n" . implode(";\n", $dataDiff)
        );
    }

    /**
     * Verifying that form is filled correctly.
     *
     * @param array $formData
     * @param array $fixtureData
     * @return array
     */
    protected function verifyForm(array $formData, array $fixtureData)
    {
        $errorMessages = [];
        foreach ($fixtureData as $key => $value) {
            if (in_array($key, $this->skippedFields)) {
                continue;
            } elseif ($key === 'resources') {
                $errorMessages = array_merge(
                    $errorMessages,
                    $this->checkResources($formData[$key], $fixtureData[$key])
                );
            } elseif ($value !== $formData[$key]) {
                $errorMessages[] = $this->getErrorMessage($value, $formData[$key], $key);
            }
        }

        return $errorMessages;
    }

    /**
     * Check resources errors.
     *
     * @param array $formData
     * @param array|string $fixtureData
     * @return array
     */
    protected function checkResources(array $formData, $fixtureData)
    {
        $errorMessages = [];
        $diff = $this->getResourcesDifferentData($formData, $fixtureData);
        if (array_filter($diff)) {
            $errorMessages[] = $this->getErrorMessage($fixtureData, $formData, 'resources');
        }

        return $errorMessages;
    }

    /**
     * Get different data between form and fixture data.
     *
     * @param array $formData
     * @param array|string $fixtureData
     * @return array
     */
    protected function getResourcesDifferentData(array $formData, $fixtureData)
    {
        $fixtureData = is_array($fixtureData) ? $fixtureData : [$fixtureData];
        return $this->strictResourcesVerify
            ? array_diff($formData, $fixtureData)
            : $this->notStrictVerification($formData, $fixtureData);
    }

    /**
     * Not strict verify resources data.
     *
     * @param array $formData
     * @param array $fixtureData
     * @return array
     */
    protected function notStrictVerification(array $formData, array $fixtureData)
    {
        $diff = [];
        foreach ($fixtureData as $itemData) {
            $diff[] = in_array($itemData, $formData) ? null : true;
        }

        return $diff;
    }

    /**
     * Get error message.
     *
     * @param mixed $fixtureData
     * @param mixed $formData
     * @param mixed $field
     * @return string
     */
    protected function getErrorMessage($fixtureData, $formData, $field)
    {
        $fixtureData = is_array($fixtureData) ? $this->arrayToString($fixtureData) : $fixtureData;
        $formData = is_array($formData) ? $this->arrayToString($formData) : $formData;
        return sprintf($this->errorMessagePattern, $field, $fixtureData, $formData);
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Integration form was filled correctly.';
    }
}
