<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ContactGraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for sending a contact form via GraphQL
 * @magentoAppArea graphql
 */
class SubmitContactFormTest extends GraphQlAbstract
{
    /**
     * @magentoConfigFixture default_store contact/contact/enabled 1
     */
    public function testSendingContactFormWithContactModuleEnabled(): void
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query);

        $this->assertTrue($response['submitContactForm']['success']);
    }

    /**
     * @magentoConfigFixture default_store contact/contact/enabled 0
     */
    public function testSendingContactFormWithContactModuleDisabled(): void
    {
        $query = $this->getQuery();
        $this->expectException(\Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException::class);

        $this->graphQlMutation($query);
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
        mutation {
            submitContactForm(
                input: {
                    name: "John Doe",
                    email: "johndoe@example.com",
                    telephone: "123123123",
                    comment: "I like donuts."
                }
            ) {
                success
            }
        }
        QUERY;
    }
}
