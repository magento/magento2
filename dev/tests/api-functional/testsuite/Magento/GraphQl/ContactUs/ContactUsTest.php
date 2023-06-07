<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ContactUs;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

#[
    Config("contact/contact/enabled", "1")
]
class ContactUsTest extends GraphQlAbstract
{
    /**
     * Successfuly send contact us form
     */
    public function testContactUsSuccess()
    {
        $query = <<<MUTATION
mutation {
    contactUs(input: {
        comment:"Test Contact Us",
        email:"test@adobe.com",
        name:"John Doe",
        telephone:"1111111111"
    })
    {
        status
    }
}
MUTATION;

        $expected = [
            "contactUs" => [
                "status" => true
            ]
        ];
        $response = $this->graphQlMutation($query, [], '', []);
        $this->assertEquals($expected, $response, "Contact Us form can not be send");
    }

    /**
     * Failed send contact us form - missing email
     */
    public function testContactUsBadEmail()
    {
        $query = <<<MUTATION
mutation {
    contactUs(input: {
        comment:"Test Contact Us",
        name:"John Doe",
        email:"adobe.com",
        telephone:"1111111111"
    })
    {
        status
    }
}
MUTATION;
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: The email address is invalid. Verify the email address and try again.'
        );
        $this->graphQlMutation($query, [], '', []);
    }

    /**
     * Failed send contact us form - missing name
     */
    public function testContactUsMissingName()
    {
        $query = <<<MUTATION
mutation {
    contactUs(input: {
        comment:"Test Contact Us",
        email:"test@adobe.com",
        telephone:"1111111111"
    })
    {
        status
    }
}
MUTATION;
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Field ContactUsInput.name of required type String! was not provided.'
        );
        $this->graphQlMutation($query, [], '', []);
    }

    /**
     * Failed send contact us form - missing name
     */
    public function testContactUsMissingComment()
    {
        $query = <<<MUTATION
mutation {
    contactUs(input: {
        email:"test@adobe.com",
        name:"John Doe",
        telephone:"1111111111"
    })
    {
        status
    }
}
MUTATION;
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Field ContactUsInput.comment of required type String! was not provided.'
        );
        $this->graphQlMutation($query, [], '', []);
    }

    /**
     * Failed send contact us form - missing name
     */
    #[
        Config("contact/contact/enabled", "0")
    ]
    public function testContactUsDisabled()
    {
        $query = <<<MUTATION
mutation {
    contactUs(input: {
        comment:"Test Contact Us",
        email:"test@adobe.com",
        name:"John Doe",
        telephone:"1111111111"
    })
    {
        status
    }
}
MUTATION;

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The contact form is unavailable.');
        $this->graphQlMutation($query, [], '', []);
    }
}
