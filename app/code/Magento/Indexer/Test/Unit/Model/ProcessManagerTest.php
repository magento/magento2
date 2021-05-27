<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Indexer\Model\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * Class covers process manager execution test logic
 */
class ProcessManagerTest extends TestCase
{
    /**
     * @dataProvider functionsWithErrorProvider
     * @param array $userFunctions
     * @param int $threadsCount
     * @return void
     */
    public function testFailureInChildProcessHandleMultiThread(array $userFunctions, int $threadsCount): void
    {
        $connectionMock = $this->createMock(ResourceConnection::class);
        $processManager = new ProcessManager(
            $connectionMock,
            null,
            $threadsCount
        );

        try {
            $processManager->execute($userFunctions);
            $this->fail('Exception was not handled');
        } catch (\RuntimeException $exception) {
            $this->assertEquals('Fail in child process', $exception->getMessage());
        }
    }

    /**
     * Closure functions data provider for multi thread execution
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function functionsWithErrorProvider(): array
    {
        return [
            'more_threads_than_functions' => [
                'user_functions' => [
                    // @codingStandardsIgnoreStart
                    function () {
                        exit(1);
                    },
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    // @codingStandardsIgnoreEnd
                ],
                'threads_count' => 4,
            ],
            'less_threads_than_functions' => [
                'user_functions' => [
                    // @codingStandardsIgnoreStart
                    function () {
                        exit(1);
                    },
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    // @codingStandardsIgnoreEnd
                ],
                'threads_count' => 2,
            ],
            'equal_threads_and_functions' => [
                'user_functions' => [
                    // @codingStandardsIgnoreStart
                    function () {
                        exit(1);
                    },
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    // @codingStandardsIgnoreEnd
                ],
                'threads_count' => 3,
            ],
        ];
    }

    /**
     * @dataProvider successFunctionsProvider
     * @param array $userFunctions
     * @param int $threadsCount
     * @return void
     */
    public function testSuccessChildProcessHandleMultiThread(array $userFunctions, int $threadsCount): void
    {
        $connectionMock = $this->createMock(ResourceConnection::class);
        $processManager = new ProcessManager(
            $connectionMock,
            null,
            $threadsCount
        );

        try {
            $processManager->execute($userFunctions);
        } catch (\RuntimeException $exception) {
            $this->fail('Exception was not handled');
        }
    }

    /**
     * Closure functions data provider for multi thread execution
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function successFunctionsProvider(): array
    {
        return [
            'more_threads_than_functions' => [
                'user_functions' => [
                    // @codingStandardsIgnoreStart
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    // @codingStandardsIgnoreEnd
                ],
                'threads_count' => 4,
            ],
            'less_threads_than_functions' => [
                'user_functions' => [
                    // @codingStandardsIgnoreStart
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    // @codingStandardsIgnoreEnd
                ],
                'threads_count' => 2,
            ],
            'equal_threads_and_functions' => [
                'user_functions' => [
                    // @codingStandardsIgnoreStart
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    function () {
                        exit(0);
                    },
                    // @codingStandardsIgnoreEnd
                ],
                'threads_count' => 3,
            ],
        ];
    }
}
