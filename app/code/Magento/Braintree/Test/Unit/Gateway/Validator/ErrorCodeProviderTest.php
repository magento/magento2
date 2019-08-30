<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Validator;

use Braintree\Result\Error;
use Magento\Braintree\Gateway\Validator\ErrorCodeProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class ErrorCodeProviderTest
 */
class ErrorCodeProviderTest extends TestCase
{
    /**
     * @var ErrorCodeProvider
     */
    private $model;

    /**
     * Checks a extracting error codes from response.
     *
     * @param array $errors
     * @param array $transaction
     * @param array $expectedResult
     * @return void
     * @dataProvider getErrorCodeDataProvider
     */
    public function testGetErrorCodes(array $errors, array $transaction, array $expectedResult): void
    {
        $response = new Error(
            [
                'errors' => ['errors' => $errors],
                'transaction' => $transaction,
            ]
        );
        $this->model = new ErrorCodeProvider();
        $actual = $this->model->getErrorCodes($response);

        $this->assertSame($expectedResult, $actual);
    }

    /**
     * Gets list of errors variations.
     *
     * @return array
     */
    public function getErrorCodeDataProvider(): array
    {
        return [
            [
                'errors' => [
                    ['code' => 91734],
                    ['code' => 91504]
                ],
                'transaction' => [
                    'status' => 'success',
                ],
                'expectedResult' => ['91734', '91504']
            ],
            [
                'errors' => [],
                'transaction' => [
                    'status' => 'processor_declined',
                    'processorResponseCode' => '1000'
                ],
                'expectedResult' => ['1000']
            ],
            [
                'errors' => [],
                'transaction' => [
                    'status' => 'processor_declined',
                    'processorResponseCode' => '1000'
                ],
                'expectedResult' => ['1000']
            ],
            [
                'errors' => [
                    ['code' => 91734],
                    ['code' => 91504]
                ],
                'transaction' => [
                    'status' => 'processor_declined',
                    'processorResponseCode' => '1000'
                ],
                'expectedResult' => ['91734', '91504', '1000']
            ],
        ];
    }
}
