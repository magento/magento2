<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess;

use Magento\Framework\ParallelProcess\Fork\PcntlForkManager;
use Magento\Framework\ParallelProcess\Process\Data;
use Magento\Framework\ParallelProcess\Process\ExitedWithErrorException;
use Magento\Framework\ParallelProcess\Process\RunnerInterface;
use PHPUnit\Framework\TestCase;

class ProcessManagerTest extends TestCase
{
    public function testRun()
    {
        $runner = new class implements RunnerInterface {
            /**
             * @inheritDoc
             */
            public function run(array $data)
            {
                usleep(rand (50, 200));
                echo PHP_EOL.$data['id'].PHP_EOL;
            }
        };
        $fork = new PcntlForkManager();
        $successful = [];
        $failed = [];
        for ($i = 1; $i <= 10; $i++) {
            if (rand(0,1)) {
                $dependsOn = ['id'.rand(1,10)];
                if ($dependsOn[0] === 'id'.$i) {
                    $dependsOn = [];
                }
            } else {
                $dependsOn = [];
            }
            $successful[] = new Data('id' . $i, ['id'=>'id'.$i], $dependsOn);
        }
        $failed[] = new Data('idFailed', []);

        $manager = new ProcessManager($runner, $fork, array_merge($successful, $failed), 8);
        try {
            $manager->run();
        } catch (ExitedWithErrorException $exception) {
            $this->assertEquals($failed, $exception->getFailedProcesses());
        }
    }
}