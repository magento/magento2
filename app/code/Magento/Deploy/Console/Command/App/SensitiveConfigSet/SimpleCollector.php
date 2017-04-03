<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\SensitiveConfigSet;

use Magento\Deploy\Console\Command\App\SensitiveConfigSetCommand;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\QuestionFactory;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class SimpleCollector collects configuration value from user input
 */
class SimpleCollector implements CollectorInterface
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
     * Collects single configuration value from user input
     *
     * For example, this method will return
     * ```php
     * ['some/configuration/path' => 'someValue']
     * ```
     * {@inheritdoc}
     */
    public function getValues(InputInterface $input, OutputInterface $output, array $configPaths)
    {
        $inputPath = $input->getArgument(SensitiveConfigSetCommand::INPUT_ARGUMENT_PATH);
        $configPathQuestion = $this->getConfigPathQuestion($configPaths);
        $configPath = ($inputPath === null)
            ? $this->questionHelper->ask($input, $output, $configPathQuestion)
            : $inputPath;

        $this->validatePath($configPath, $configPaths);

        $inputValue = $input->getArgument(SensitiveConfigSetCommand::INPUT_ARGUMENT_VALUE);
        $configValueQuestion = $this->getConfigValueQuestion();
        $configValue = $inputValue === null
            ? $this->questionHelper->ask($input, $output, $configValueQuestion)
            : $inputValue;

        return [$configPath => $configValue];
    }

    /**
     * Get Question to fill configuration path with autocompletion in interactive mode
     *
     * @param array $configPaths
     * @return Question
     */
    private function getConfigPathQuestion(array $configPaths)
    {
        /** @var Question $configPathQuestion */
        $configPathQuestion = $this->questionFactory->create([
            'question' => 'Please enter config path: '
        ]);
        $configPathQuestion->setAutocompleterValues($configPaths);
        $configPathQuestion->setValidator(function ($configPath) use ($configPaths) {
            $this->validatePath($configPath, $configPaths);
            return $configPath;
        });

        return $configPathQuestion;
    }

    /**
     * Get Question to fill configuration value in interactive mode
     *
     * @return Question
     */
    private function getConfigValueQuestion()
    {
        /** @var Question $configValueQuestion */
        $configValueQuestion = $this->questionFactory->create([
            'question' => 'Please enter value: '
        ]);
        $configValueQuestion->setValidator(function ($interviewer) {
            if (empty($interviewer)) {
                throw new LocalizedException(new Phrase('Value can\'t be empty'));
            }
            return $interviewer;
        });

        return $configValueQuestion;
    }

    /**
     * Check if entered configuration path is valid, throw LocalizedException otherwise
     *
     * @param string $configPath Path that should be validated.
     * @param array $configPaths List of allowed paths.
     * @return void
     * @throws LocalizedException If config path not exist in allowed config paths
     */
    private function validatePath($configPath, array $configPaths)
    {
        if (!in_array($configPath, $configPaths)) {
            throw new LocalizedException(
                new Phrase('A configuration with this path does not exist or is not sensitive')
            );
        }
    }
}
