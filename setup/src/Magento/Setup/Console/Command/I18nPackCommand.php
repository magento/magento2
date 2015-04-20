<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Setup\Module\I18n\ServiceLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for i18n language packaging
 */
class I18nPackCommand extends Command
{
    /**#@+
     * Keys and shortcuts for input arguments and options
     */
    const INPUT_KEY_SOURCE = 'source';
    const INPUT_KEY_PACK = 'pack';
    const INPUT_KEY_LOCALE = 'locale';
    const INPUT_KEY_MODE = 'mode';
    const SHORTCUT_KEY_MODE = 'm';
    const INPUT_KEY_ALLOW_DUPLICATES = 'allow-duplicates';
    const SHORTCUT_KEY_ALLOW_DUPLICATES = 'd';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('i18n:pack')
            ->setDescription('Saves language package');
        $this->setDefinition([
            new InputArgument(
                self::INPUT_KEY_SOURCE,
                InputArgument::REQUIRED,
                'Path to source dictionary file with translations'
            ),
            new InputArgument(
                self::INPUT_KEY_PACK,
                InputArgument::REQUIRED,
                'Path to language package'
            ),
            new InputArgument(
                self::INPUT_KEY_LOCALE,
                InputArgument::REQUIRED,
                'Target locale for dictionary, for example "de_DE"'
            ),
            new InputOption(
                self::INPUT_KEY_MODE,
                self::SHORTCUT_KEY_MODE,
                InputOption::VALUE_REQUIRED,
                'Save mode for dictionary' . PHP_EOL . '- "replace" - replace language pack by new one' . PHP_EOL .
                '- "merge" - merge language packages, by default "replace"'
            ),
            new InputOption(
                self::INPUT_KEY_ALLOW_DUPLICATES,
                self::SHORTCUT_KEY_ALLOW_DUPLICATES,
                InputOption::VALUE_NONE,
                'Is allowed to save duplicates of translate, by default "no"'
            ),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $generator = ServiceLocator::getPackGenerator();
            $locale = $input->getArgument(self::INPUT_KEY_LOCALE);
            $generator->generate(
                $input->getArgument(self::INPUT_KEY_SOURCE),
                $input->getArgument(self::INPUT_KEY_PACK),
                $locale,
                $input->getOption(self::INPUT_KEY_MODE),
                $input->getOption(self::INPUT_KEY_ALLOW_DUPLICATES)
            );
            $output->writeln("Successfully saved $locale language package.");
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
