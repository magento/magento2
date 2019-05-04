<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    const INPUT_KEY_LOCALE = 'locale';
    const INPUT_KEY_MODE = 'mode';
    const INPUT_KEY_ALLOW_DUPLICATES = 'allow-duplicates';
    /**#@-*/

    /**
     * 'replace' mode value
     */
    const MODE_REPLACE = 'replace';

    /**
     * 'merge' mode value
     */
    const MODE_MERGE = 'merge';

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
                self::INPUT_KEY_LOCALE,
                InputArgument::REQUIRED,
                'Target locale for dictionary, for example "de_DE"'
            ),
            new InputOption(
                self::INPUT_KEY_MODE,
                'm',
                InputOption::VALUE_REQUIRED,
                'Save mode for dictionary' . PHP_EOL . '- "replace" - replace language pack by new one' . PHP_EOL .
                '- "merge" - merge language packages, by default "replace"',
                self::MODE_REPLACE
            ),
            new InputOption(
                self::INPUT_KEY_ALLOW_DUPLICATES,
                'd',
                InputOption::VALUE_NONE,
                'Use the --allow-duplicates parameter to allow saving duplicates of translate.' .
                ' Otherwise omit the parameter.'
            ),
        ]);
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = ServiceLocator::getPackGenerator();
        $mode = $input->getOption(self::INPUT_KEY_MODE);
        if ($mode !== self::MODE_MERGE && $mode !== self::MODE_REPLACE) {
            throw new \InvalidArgumentException("Possible values for 'mode' option are 'replace' and 'merge'");
        }
        $locale = $input->getArgument(self::INPUT_KEY_LOCALE);
        $generator->generate(
            $input->getArgument(self::INPUT_KEY_SOURCE),
            $locale,
            $input->getOption(self::INPUT_KEY_MODE),
            $input->getOption(self::INPUT_KEY_ALLOW_DUPLICATES)
        );
        $output->writeln("<info>Successfully saved $locale language package.</info>");
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
