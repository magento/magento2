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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HookInstallCommand extends Command
{
    const ARG_SOURCE = 'source';
    const ARG_TARGET = 'target';

    protected function configure()
    {
        $this->setName('hook:install');

        $this->setDescription('Symlink a hook to the given target.');

        $this->addArgument(
            self::ARG_SOURCE,
            InputArgument::REQUIRED,
            'The hook to link, either a path to a file or the filename of a hook in the hooks folder.'
        );

        $this->addArgument(
            self::ARG_TARGET,
            InputArgument::REQUIRED,
            'The target location, including the filename (e.g. .git/hooks/pre-commit).'
        );

        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overrite any existing files at the symlink target.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = realpath($input->getArgument(self::ARG_SOURCE));
        $target = $input->getArgument(self::ARG_TARGET);
        $force  = $input->getOption('force');

        if ($output->isVeryVerbose()) {
            $message = sprintf('<info>Using %s as the hook.</info>', $source);
            $output->writeln($message);

            $message = sprintf('<info>Using %s for the install path.</info>', $target);
            $output->writeln($message);
        }

        if (! file_exists($source)) {
            $error = sprintf('<error>The hook %s does not exist!</error>', $source);
            $output->writeln($error);
            exit(1);
        }

        if (! is_dir(dirname($target))) {
            $message = sprintf('<error>The directory at %s does not exist.</error>', $target);
            $output->writeln($message);
            exit(1);
        }

        if (file_exists($target) && $force) {
            unlink($target);

            $message = sprintf('<comment>Removed existing file at %s.</comment>', $target);
            $output->writeln($message);
        }

        if (! file_exists($target) || $force) {
            symlink($source, $target);
            chmod($target, 0755);
            $output->writeln('Symlink created.');
        } else {
            $message = sprintf('<error>A file at %s already exists.</error>', $target);
            $output->writeln($message);
            exit(1);
        }
    }
}
