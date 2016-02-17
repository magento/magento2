<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2014 Samuel Parkinson <@samparkinson_>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE.md
 */

namespace StaticReview\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class HookRunCommand extends Command
{
    const ARGUMENT_HOOK = 'hook';

    protected function configure()
    {
        $this->setName('hook:run');

        $this->setDescription('Run the specified hook.');

        $this->addArgument(self::ARGUMENT_HOOK, InputArgument::REQUIRED, 'The hook file to run.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hookArg = $input->getArgument(self::ARGUMENT_HOOK);
        $path = $this->getTargetPath($hookArg);

        if (file_exists($path)) {
            $cmd = 'php ' . $path;

            $process = new Process($cmd);

            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });
        }
    }

    /**
     * @param $hookArgument string
     * @return string
     */
    protected function getTargetPath($hookArgument)
    {
        if (file_exists($hookArgument)) {
            $target = realpath($hookArgument);
        } else {
            $path = '%s/%s.php';
            $target = sprintf($path, realpath(__DIR__ . '/../../hooks/'), $hookArgument);
        }

        if (! file_exists($target)) {
            $error = sprintf('<error>The hook %s does not exist!</error>', $target);
            $output->writeln($error);
            exit(1);
        }

        return $target;
    }
}
