<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\SensitiveConfigSet;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\QuestionFactory;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class InteractiveCollector collects configuration values from user input
 */
class InteractiveCollector implements CollectorInterface
{
    /**
     * @var QuestionFactory
     */
    private $questionFactory;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    /**
     * @param QuestionFactory $questionFactory
     * @param QuestionHelper $questionHelper
     */
    public function __construct(
        QuestionFactory $questionFactory,
        QuestionHelper $questionHelper
    ) {
        $this->questionFactory = $questionFactory;
        $this->questionHelper = $questionHelper;
    }

    /**
     * Collect list of configuration values from user input
     *
     * For example, this method will return
     *
     * ```php
     * [
     *     'some/configuration/path1' => 'someValue1',
     *     'some/configuration/path2' => 'someValue2',
     *     'some/configuration/path3' => 'someValue3',
     * ]
     * ```
     * {@inheritdoc}
     */
    public function getValues(InputInterface $input, OutputInterface $output, array $configPaths)
    {
        $output->writeln('<info>Please set configuration values or skip them by pressing [Enter]:</info>');
        $values = [];
        foreach ($configPaths as $configPath) {
            $question = $this->questionFactory->create([
                'question' => $configPath . ': '
            ]);
            $values[$configPath] = $this->questionHelper->ask($input, $output, $question);
        }

        return $values;
    }
}
