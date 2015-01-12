<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertIntegrationTokensAfterReauthorize
 * Assert that Access tokens was changed correctly after Reauthorize.
 */
class AssertIntegrationTokensAfterReauthorize extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Fields don't have to change.
     *
     * @var array
     */
    protected $consumerKeys = [
        'key',
        'consumer_secret',
    ];

    /**
     * Fields had to change.
     *
     * @var array
     */
    protected $accessTokens = [
        'token',
        'token_secret',
    ];

    /**
     * Assert that Access tokens was changed correctly after Reauthorize.
     * Checking fields:
     * - Consumer Key;
     * - Consumer Secret;
     * - Access Token;
     * - Access Token Secret.
     *
     * @param IntegrationIndex $integrationIndex
     * @param IntegrationNew $integrationNew
     * @param Integration $integration
     * @return void
     */
    public function processAssert(
        IntegrationIndex $integrationIndex,
        IntegrationNew $integrationNew,
        Integration $integration
    ) {
        $filter = ['name' => $integration->getName()];
        $integrationIndex->open();
        $integrationIndex->getIntegrationGrid()->searchAndOpen($filter);
        $actualData = $integrationNew->getIntegrationForm()->getData();
        $errors = $this->checkTokens($actualData, $integration->getData());

        \PHPUnit_Framework_Assert::assertEmpty(
            $errors,
            "Integration tokens was changed incorrectly.\nLog:\n" . implode(";\n", $errors)
        );
    }

    /**
     * Check tokens was changed correctly.
     *
     * @param array $actualData
     * @param array $tokens
     * @return array
     */
    protected function checkTokens(array $actualData, array $tokens)
    {
        $errors = [];
        foreach ($this->consumerKeys as $consumerKey) {
            if ($actualData[$consumerKey] !== $tokens[$consumerKey]) {
                $errors[] = "Field '" . $consumerKey . "' was changed.";
            }
        }
        foreach ($this->accessTokens as $accessToken) {
            if ($actualData[$accessToken] === $tokens[$accessToken]) {
                $errors[] = "Field '" . $accessToken . "' was not changed.";
            }
        }
        return $errors;
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Access tokens were reauthorized correctly.';
    }
}
