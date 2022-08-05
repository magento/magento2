<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SecureTokenTest
 *
 * Test class for \Magento\Paypal\Model\Payflow\Service\Response\Validator\SecureToken
 */
class SecureTokenTest extends TestCase
{
    /**
     * @var SecureToken
     */
    protected $validator;

    /**
     * @var Transparent|MockObject
     */
    protected $payflowFacade;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->validator = new SecureToken();
        $this->payflowFacade = $this->getMockBuilder(Transparent::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    /**
     * @param bool $result
     * @param DataObject $response
     *
     * @dataProvider validationDataProvider
     */
    public function testValidation($result, DataObject $response)
    {
        $this->assertEquals($result, $this->validator->validate($response, $this->payflowFacade));
    }

    /**
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            [
                'result' => true,
                'response' => new DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => 0 // - good code
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => SecureToken::ST_ALREADY_USED
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => SecureToken::ST_EXPIRED
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => SecureToken::ST_TRANSACTION_IN_PROCESS
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new DataObject(
                    [
                        'securetoken' => 'kcsakc;lsakc;lksa;kcsa;',
                        'result' => 'BAD_CODE'
                    ]
                )
            ],
            [
                'result' => false,
                'response' => new DataObject(
                    [
                        'securetoken' => null, // -
                        'result' => SecureToken::ST_TRANSACTION_IN_PROCESS
                    ]
                )
            ]
        ];
    }
}
