<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertIntegrationForm
 * Assert that integration form filled correctly
 */
class AssertIntegrationForm extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Skipped fields while verifying
     *
     * @var array
     */
    protected $skippedFields = [
        'integration_id',
    ];

    /**
     * Assert that integration form filled correctly
     *
     * @param IntegrationIndex $integrationIndexPage
     * @param IntegrationNew $integrationNewPage
     * @param Integration $integration
     * @param Integration|null $initialIntegration
     * @return void
     */
    public function processAssert(
        IntegrationIndex $integrationIndexPage,
        IntegrationNew $integrationNewPage,
        Integration $integration,
        Integration $initialIntegration = null
    ) {
        $data = ($initialIntegration === null)
            ? $integration->getData()
            : array_merge($initialIntegration->getData(), $integration->getData());
        $filter = [
            'name' => $data['name'],
        ];

        $integrationIndexPage->open();
        $integrationIndexPage->getIntegrationGrid()->searchAndOpen($filter);
        $formData = $integrationNewPage->getIntegrationForm()->getData();
        $dataDiff = $this->verifyForm($formData, $data);
        \PHPUnit_Framework_Assert::assertEmpty(
            $dataDiff,
            'Integration form was filled incorrectly.'
            . "\nLog:\n" . implode(";\n", $dataDiff)
        );
    }

    /**
     * Verifying that form is filled correctly
     *
     * @param array $formData
     * @param array $fixtureData
     * @return array $errorMessages
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function verifyForm(array $formData, array $fixtureData)
    {
        $issetResources = [];
        $errorMessages = [];
        $errorMessage = "Data in '%s' field not equal.\nExpected: %s\nActual: %s";

        foreach ($fixtureData as $key => $value) {
            if (in_array($key, $this->skippedFields)) {
                continue;
            }
            if ($key === 'resources') {
                $fixtureData[$key] = is_array($fixtureData[$key]) ? $fixtureData[$key] : [$fixtureData[$key]];
                foreach ($fixtureData[$key] as $fixtureResource) {
                    foreach ($formData[$key] as $formResource) {
                        if (preg_match('|^' . preg_quote($fixtureResource) . '|', $formResource)) {
                            $issetResources[] = $formResource;
                        }
                    }
                }
                $diff = array_diff($formData[$key], $issetResources);
                if (!empty($diff)) {
                    $errorMessages[] = sprintf(
                        $errorMessage,
                        $key,
                        implode(",\n", $fixtureData[$key]),
                        implode(",\n", $formData[$key])
                    );
                }
            } elseif ($value !== $formData[$key]) {
                $errorMessages[] = sprintf($errorMessage, $key, $value, $formData[$key]);
            }
        }

        return $errorMessages;
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Integration form was filled correctly.';
    }
}
