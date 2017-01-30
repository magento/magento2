<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Setup\Module\I18n\ServiceLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for i18n dictionary generation
 */
class I18nCollectPhrasesCommand extends Command
{
    /**#@+
     * Keys and shortcuts for input arguments and options
     */
    const INPUT_KEY_DIRECTORY = 'directory';
    const INPUT_KEY_OUTPUT = 'output';
    const SHORTCUT_KEY_OUTPUT = 'o';
    const INPUT_KEY_MAGENTO = 'magento';
    const SHORTCUT_KEY_MAGENTO = 'm';
    /**#@- */

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('i18n:collect-phrases')
            ->setDescription('Discovers phrases in the codebase');
        $this->setDefinition([
            new InputArgument(self::INPUT_KEY_DIRECTORY, InputArgument::REQUIRED, 'Directory path to parse'),
            new InputOption(
                self::INPUT_KEY_OUTPUT,
                self::SHORTCUT_KEY_OUTPUT,
                InputOption::VALUE_REQUIRED,
                'Path (including filename) to an output file. With no file specified, defaults to stdout.'
            ),
            new InputOption(
                self::INPUT_KEY_MAGENTO,
                self::SHORTCUT_KEY_MAGENTO,
                InputOption::VALUE_NONE,
                'Use the --magento parameter to specify the directory is the Magento root directory.' .
                ' Omit the parameter if the directory is not the Magento root directory.'
            ),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = ServiceLocator::getDictionaryGenerator();
        $generator->generate(
            $input->getArgument(self::INPUT_KEY_DIRECTORY),
            $input->getOption(self::INPUT_KEY_OUTPUT),
            $input->getOption(self::INPUT_KEY_MAGENTO)
        );
        $output->writeln('<info>Dictionary successfully processed.</info>');
    }
}
