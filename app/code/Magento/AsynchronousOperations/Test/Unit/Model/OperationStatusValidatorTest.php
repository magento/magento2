<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Model\OperationStatusValidator;
use Magento\AsynchronousOperations\Model\Operation;
use Magento\AsynchronousOperations\Model\OperationStatusPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class OperationStatusValidatorTest
 */
class OperationStatusValidatorTest extends TestCase
{
    /**
     * @var OperationStatusPool
     */
    private $operationStatusPool;

    /**
     * @var OperationStatusValidator
     */
    private $operationStatusValidator;

    /**
     * @var Operation
     */
    private $operation;

    protected function setUp()
    {
        $this->operationStatusPool = $this->getMockBuilder(OperationStatusPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->operationStatusValidator = $objectManager->getObject(
            OperationStatusValidator::class,
            [
                'operationStatusPool' => $this->operationStatusPool
            ]
        );

        $this->operation = $objectManager->getObject(
            Operation::class,
            [
                'operationStatusValidator' => $this->operationStatusValidator
            ]
        );
    }

    /**
     * @param string $status
     * @param array $statusPool
     * @param string $expectedResult
     * @dataProvider dataProviderForTestSetStatus
     */
    public function testSetStatus (
        string $status,
        array $statusPool,
        string $expectedResult
    ) {
        $this->operationStatusPool
            ->expects($this->any())
            ->method('getStatuses')
            ->willReturn($statusPool);

        try {
            $this->operation->setStatus($status);
            $this->assertEquals($expectedResult, $this->operation->getStatus());
        } catch (\Exception $exception) {
            $this->assertEquals($expectedResult, $exception->getMessage());
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProviderForTestSetStatus()
    {
        return [
            [
                'status' => 0,
                'statusPool' => [
                    'complete' => 1,
                    'retriablyFailed' => 2,
                    'notRetriablyFailed' => 3,
                    'open' => 4,
                    'rejected' => 5
                ],
                'expectedResult' => 'Invalid Operation Status.'
            ],
            [
                'status' => 1,
                'statusPool' => [
                    'complete' => 1,
                    'retriablyFailed' => 2,
                    'notRetriablyFailed' => 3,
                    'open' => 4,
                    'rejected' => 5
                ],
                'expectedResult' => 1
            ],
            [
                'status' => 2,
                'statusPool' => [
                    'complete' => 1,
                    'retriablyFailed' => 2,
                    'notRetriablyFailed' => 3,
                    'open' => 4,
                    'rejected' => 5
                ],
                'expectedResult' => 2
            ],
            [
                'status' => 3,
                'statusPool' => [
                    'complete' => 1,
                    'retriablyFailed' => 2,
                    'notRetriablyFailed' => 3,
                    'open' => 4,
                    'rejected' => 5
                ],
                'expectedResult' => 3
            ],
            [
                'status' => 4,
                'statusPool' => [
                    'complete' => 1,
                    'retriablyFailed' => 2,
                    'notRetriablyFailed' => 3,
                    'open' => 4,
                    'rejected' => 5
                ],
                'expectedResult' => 4
            ],
            [
                'status' => 5,
                'statusPool' => [
                    'complete' => 1,
                    'retriablyFailed' => 2,
                    'notRetriablyFailed' => 3,
                    'open' => 4,
                    'rejected' => 5
                ],
                'expectedResult' => 5
            ]
        ];
    }
}
