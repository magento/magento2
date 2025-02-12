<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Console\Command;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\ResourceModel\Indexer\State;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for setting index status for indexers.
 */
class IndexerSetStatusCommand extends AbstractIndexerManageCommand
{
    /**#@+
     * Names of input arguments or options
     */
    private const INPUT_KEY_STATUS = 'status';
    /**#@- */

    /**
     * @var State
     */
    private State $stateResourceModel;

    /**
     * @param State $stateResourceModel
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        State                $stateResourceModel,
        ObjectManagerFactory $objectManagerFactory
    ) {
        $this->stateResourceModel = $stateResourceModel;
        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('indexer:set-status')
            ->setDescription('Sets the specified indexer status')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);
        if ($errors) {
            throw new \InvalidArgumentException(implode("\n", $errors));
        }

        $newStatus = $input->getArgument(self::INPUT_KEY_STATUS);
        $indexers = $this->getIndexers($input);
        $returnValue = Cli::RETURN_SUCCESS;

        foreach ($indexers as $indexer) {
            try {
                $this->updateIndexerStatus($indexer, $newStatus, $output);
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $returnValue = Cli::RETURN_FAILURE;
            }
        }

        return $returnValue;
    }

    /**
     * Gets list of arguments for the command.
     *
     * @return InputOption[]
     */
    public function getInputList(): array
    {
        $modeOptions[] = new InputArgument(
            self::INPUT_KEY_STATUS,
            InputArgument::REQUIRED,
            'Indexer status type [' . StateInterface::STATUS_INVALID
            . '|' . StateInterface::STATUS_SUSPENDED . '|' . StateInterface::STATUS_VALID . ']'
        );

        return array_merge($modeOptions, parent::getInputList());
    }

    /**
     * Checks if all CLI command options are provided.
     *
     * @param InputInterface $input
     * @return string[]
     */
    private function validate(InputInterface $input): array
    {
        $errors = [];
        $acceptedValues = [
            StateInterface::STATUS_INVALID,
            StateInterface::STATUS_SUSPENDED,
            StateInterface::STATUS_VALID
        ];
        $inputStatus = $input->getArgument(self::INPUT_KEY_STATUS);

        if (!in_array($inputStatus, $acceptedValues, true)) {
            $acceptedValuesString = '"' . implode('", "', $acceptedValues) . '"';
            $errors[] = sprintf(
                'Invalid status "%s". Accepted values are %s.',
                $inputStatus,
                $acceptedValuesString
            );
        }

        return $errors;
    }

    /**
     * Updates the status of a specified indexer.
     *
     * @param IndexerInterface $indexer
     * @param string $newStatus
     * @param OutputInterface $output
     * @return void
     * @throws AlreadyExistsException
     */
    private function updateIndexerStatus(IndexerInterface $indexer, string $newStatus, OutputInterface $output): void
    {
        $state = $indexer->getState();
        $previousStatus = $state->getStatus();
        $this->stateResourceModel->save($state->setStatus($newStatus));
        $currentStatus = $state->getStatus();

        if ($previousStatus !== $currentStatus) {
            $output->writeln(
                sprintf(
                    "Index status for Indexer '%s' was changed from '%s' to '%s'.",
                    $indexer->getTitle(),
                    $previousStatus,
                    $currentStatus
                )
            );
        } else {
            $output->writeln(sprintf("Index status for Indexer '%s' has not been changed.", $indexer->getTitle()));
        }
    }
}
