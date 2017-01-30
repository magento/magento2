<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Paypal\Model\Payflow\Service\Response\Validator\SecureToken;

/**
 * Class SecureTokenTest
 *
 * Test class for \Magento\Paypal\Model\Payflow\Service\Response\Validator\SecureToken
 */
class SecureTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Payflow\Service\Response\Validator\SecureToken
     */
    protected $validator;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validator = new \Magento\Paypal\Model\Payflow\Service\Response\Validator\SecureToken();
    }

    /**
     * @param bool $result
     * @param \Magento\Framework\DataObject $response
     *
     * @dataProvider validationDataProvider
     */
    public function testValidation($result, \Magento\Framework\DataObject $response)
    {
        $this->assertEquals($result, $this->validator->validate($response));
    }

    /**
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            [
                'result' => true,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => 0 // - good code
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => SecureToken::ST_ALREADY_USED
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => SecureToken::ST_EXPIRED
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => SecureToken::ST_TRANSACTION_IN_PROCESS
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => 'BAD_CODE'
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new \Magento\Framework\DataObject(
                    [
                        'securetoken' => null, // -
                        'result' => SecureToken::ST_TRANSACTION_IN_PROCESS
                    ]
                )
            ]
        ];
    }
}
