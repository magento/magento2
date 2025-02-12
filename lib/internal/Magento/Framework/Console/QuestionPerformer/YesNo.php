<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console\QuestionPerformer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\QuestionFactory;

/**
 * Asks a questions to the user.
 */
class YesNo
{
    /**
     * Provides helpers to interact with the user.
     *
     * @var QuestionHelper
     */
    private $questionHelper;

    /**
     * The factory for creating Question objects.
     *
     * @var QuestionFactory
     */
    private $questionFactory;

    /**
     * @param QuestionHelper $questionHelper Provides helpers to interact with the user
     * @param QuestionFactory $questionFactory The factory for creating Question objects
     */
    public function __construct(
        QuestionHelper $questionHelper,
        QuestionFactory $questionFactory
    ) {
        $this->questionHelper = $questionHelper;
        $this->questionFactory = $questionFactory;
    }

    /**
     * Asks a question to the user. The question is generates from given array of messages.
     *
     * @param string[] $messages The array of messages for creating a question
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return bool
     */
    public function execute(array $messages, InputInterface $input, OutputInterface $output)
    {
        if (!$input->isInteractive()) {
            return true;
        }

        $question = $this->getConfirmationQuestion($messages);
        $answer = $this->questionHelper->ask($input, $output, $question);

        return in_array(strtolower($answer ?? ''), ['yes', 'y']);
    }

    /**
     * Creates Question object from given array of messages.
     *
     * @param string[] $messages array of messages
     * @return Question
     * @throws LocalizedException is thrown when a user entered a wrong answer
     */
    private function getConfirmationQuestion(array $messages)
    {
        /** @var Question $question */
        $question = $this->questionFactory->create([
            'question' => implode(PHP_EOL, $messages) . PHP_EOL
        ]);

        $question->setValidator(function ($answer) {
            if (!in_array(strtolower($answer ?? ''), ['yes', 'y', 'no', 'n'])) {
                throw new LocalizedException(
                    new Phrase('A [y]es or [n]o selection needs to be made. Select and try again.')
                );
            }

            return $answer;
        });

        return $question;
    }
}
