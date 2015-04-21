<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
            new InputArgument(self::INPUT_KEY_DIRECTORY, InputArgument::REQUIRED, 'Path to a directory to parse'),
            new InputOption(
                self::INPUT_KEY_OUTPUT,
                self::SHORTCUT_KEY_OUTPUT,
                InputOption::VALUE_REQUIRED,
                'Path (with filename) to output file, by default output the results into standard output stream'
            ),
            new InputOption(
                self::INPUT_KEY_MAGENTO,
                self::SHORTCUT_KEY_MAGENTO,
                InputOption::VALUE_NONE,
                'Flag indicates whether the specified "directory" path is a Magento root directory, false by default'
            ),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $generator = ServiceLocator::getDictionaryGenerator();
            $generator->generate(
                $input->getArgument(self::INPUT_KEY_DIRECTORY),
                $input->getOption(self::INPUT_KEY_OUTPUT),
                $input->getOption(self::INPUT_KEY_MAGENTO)
            );
            $output->writeln('Dictionary successfully processed.');
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
