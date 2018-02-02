<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess;

use Magento\Framework\App\Cache;
use Magento\Framework\ParallelProcess\Fork\PcntlForkManager;
use Magento\Framework\ParallelProcess\Process\Data;
use Magento\Framework\ParallelProcess\Process\ExitedWithErrorException;
use Magento\Framework\ParallelProcess\Process\RunnerInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class ProcessManagerTest extends TestCase
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var \ArrayObject
     */
    private $createdCacheIds;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->cache = Bootstrap::getObjectManager()->get(Cache::class);
        $this->createdCacheIds = new \ArrayObject();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        foreach ($this->createdCacheIds as $cacheId) {
            $this->cache->remove($cacheId);
        }
    }

    /**
     * @param int|null $limit Max number of processes.
     * @param array $processes
     *
     * @dataProvider getSuccessfulProcesses
     */
    public function testRun(array $processes, int $limit = null)
    {
        $cache = $this->cache;
        $createCacheId = function (string $id): string
        {
            return 'magento2_parallel_process_test_id_' . $id;
        };
        foreach ($processes as $process) {
            $this->createdCacheIds[] = $createCacheId($process['id']);
        }
        $runner = new class ($cache, $createCacheId) implements RunnerInterface {
            /**
             * @var Cache
             */
            private $cache;

            /**
             * @var callable
             */
            private $createCacheId;

            public function __construct($cache, $createId)
            {
                $this->cache = $cache;
                $this->createCacheId = $createId;
            }

            /**
             * @inheritDoc
             */
            public function run(array $data)
            {
                usleep(rand(32, 256));

                foreach ($data['depends'] as $dependsOnId) {
                    $dependencyResult = $this->cache
                        ->load(($this->createCacheId)($dependsOnId));
                    if ($dependencyResult !== 'success') {
                        throw new \RuntimeException(
                            'Provider process didn\'t successfully finish'
                        );
                    }
                }
                $id = ($this->createCacheId)($data['id']);
                $this->cache->save('success', $id);
            }
        };
        $fork = new PcntlForkManager();
        /** @var Data[] $data */
        $data = [];
        foreach ($processes as $process) {
            $data[] = new Data($process['id'], $process, $process['depends']);
        }
        $manager = new ProcessManager($runner, $fork, $data, $limit);

        $manager->run();
    }

    /**
     * @return array
     */
    public function getSuccessfulProcesses()
    {
        $processes = [
            ['id' => 'id1', 'depends' => []],
            ['id' => 'id2', 'depends' => []],
            ['id' => 'id3', 'depends' => []],
            ['id' => 'id4', 'depends' => ['id1']],
            ['id' => 'id5', 'depends' => ['id1', 'id2']],
            ['id' => 'id6', 'depends' => ['id3']],
            ['id' => 'id7', 'depends' => ['id5']],
            ['id' => 'id8', 'depends' => ['id1']],
            ['id' => 'id9', 'depends' => ['id1']],
            ['id' => 'id10', 'depends' => ['id1']],
        ];

        return [
            [
                'processes' => $processes,
                'limit' => 5
            ],
            [
                'processes' => $processes,
                'limit' => 15
            ],
            [
                'processes' => $processes,
                'limit' => 1
            ],
            [
                'processes' => $processes,
                'limit' => null
            ],
        ];
    }

    /**
     * @param int|null $limit Max number of processes.
     * @param array $processes
     * @param string[] $wasNotLaunched
     * @param string[] $finished
     *
     * @dataProvider getFailingProcesses
     */
    public function testFailures(
        array $processes,
        array $wasNotLaunched,
        array $finished,
        int $limit  =null
    ) {
        $cache = $this->cache;
        $createCacheId = function (string $id): string
        {
            return 'magento2_parallel_process_test_id_' . $id;
        };
        foreach ($processes as $process) {
            $this->createdCacheIds[] = $createCacheId($process['id']);
        }
        $runner = new class ($cache, $createCacheId) implements RunnerInterface {
            /**
             * @var Cache
             */
            private $cache;

            /**
             * @var callable
             */
            private $createCacheId;

            public function __construct($cache, $createId)
            {
                $this->cache = $cache;
                $this->createCacheId = $createId;
            }

            /**
             * @inheritDoc
             */
            public function run(array $data)
            {
                usleep(rand(32, 256));

                if ($data['depends']) {
                    foreach ($data['depends'] as $dependsOnId) {
                        if ($this->cache->load(($this->createCacheId)($dependsOnId)) !== 'success') {
                            $id = ($this->createCacheId)($data['id']);
                            $this->cache->save('failure', $id);
                            throw new \RuntimeException();
                        }
                    }
                } else {
                    if (!isset($data['success'])) {
                        throw new \RuntimeException('Failed');
                    } else {
                        $id = ($this->createCacheId)($data['id']);
                        $this->cache->save('success', $id);
                    }
                }
            }
        };
        $fork = new PcntlForkManager();
        /** @var Data[] $data */
        $data = [];
        foreach ($processes as $process) {
            $data[] = new Data($process['id'], $process, $process['depends']);
        }
        $manager = new ProcessManager($runner, $fork, $data, $limit);

        /** @var string[] $actuallyFailed */
        $actuallyFailed = [];
        try {
            $manager->run();
        } catch (ExitedWithErrorException $exception) {
            $actuallyFailed = array_map(
                function (Data $data) {
                    return $data->getId();
                },
                $exception->getFailedProcesses()
            );
        }
        foreach ($processes as $process) {
            if (!isset($process['success'])) {
                $this->assertContains($process['id'], $actuallyFailed);
            }
        }
        foreach ($wasNotLaunched as $id) {
            $this->assertFalse($this->cache->load($createCacheId($id)));
        }
        foreach ($finished as $id) {
            $this->assertNotContains($id, $actuallyFailed);
            $this->assertNotEmpty($this->cache->load($createCacheId($id)));
        }
    }

    /**
     * @return array
     */
    public function getFailingProcesses()
    {
        $processes = [];
        $processes[] = ['id' => 'id1', 'depends' => []];
        $processes[] = ['id' => 'id2', 'depends' => []];
        $processes[] = ['id' => 'id3', 'depends' => []];
        $processes[] = ['id' => 'id4', 'depends' => ['id1']];
        $processes[] = ['id' => 'id5', 'depends' => ['id1', 'id2']];
        $processes[] = ['id' => 'id6', 'depends' => ['id3']];
        $processes[] = ['id' => 'id7', 'depends' => [], 'success' => true];
        $wasNotLaunched = ['id4', 'id5', 'id6'];
        $finished = ['id7'];

        return [
            [
                'processes' => $processes,
                'wasNotLaunched' => $wasNotLaunched,
                'finished' => $finished,
                'limit' => 5,
            ],
            [
                'processes' => $processes,
                'wasNotLaunched' => $wasNotLaunched,
                'finished' => $finished,
                'limit' => 15,
            ],
            [
                'processes' => $processes,
                'wasNotLaunched' => $wasNotLaunched,
                'finished' => $finished,
                'limit' => 1,
            ],
            [
                'processes' => $processes,
                'wasNotLaunched' => $wasNotLaunched,
                'finished' => $finished,
                'limit' => null,
            ],
        ];
    }
}